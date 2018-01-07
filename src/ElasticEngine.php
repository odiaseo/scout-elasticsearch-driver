<?php

namespace SynergyScoutElastic;

use Exception;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Writer;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\Models\SearchableInterface;
use SynergyScoutElastic\Payloads\DocumentPayload;
use SynergyScoutElastic\Payloads\RawPayload;
use SynergyScoutElastic\Payloads\TypePayload;

class ElasticEngine extends Engine
{
    /**
     * @var bool
     */
    protected $updateMapping = false;

    /**
     * @var
     */
    protected $result;

    /**
     * @var ClientInterface
     */
    private $elasticClient;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var mixed
     */
    private $fields;

    /**
     * Return result from elastic search
     *
     * @var bool
     */
    private $rawResult = false;

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var Writer
     */
    private $logger;

    /**
     * ElasticEngine constructor.
     *
     * @param Kernel          $kernel
     * @param ClientInterface $elasticClient
     * @param bool            $updateMapping
     */
    public function __construct(Kernel $kernel, ClientInterface $elasticClient, bool $updateMapping)
    {
        $this->elasticClient = $elasticClient;
        $this->kernel = $kernel;
        $this->updateMapping = $updateMapping;
    }

    /**
     * @param Collection|array $models
     */
    public function update($models)
    {
        if ($models instanceof Collection) {
            return $this->bulkUpdate($models);
        }

        $models = new Collection($models);

        $models->each(function ($model) {
            /** @var $model SearchableInterface | Model */
            if ($this->updateMapping) {
                $this->kernel->call(
                    'elastic:update-mapping',
                    ['model' => get_class($model)]
                );
            }

            $array = $model->toSearchableArray();

            if (empty($array)) {
                return false;
            }

            try {
                $payload = (new DocumentPayload($model))
                    ->set('body', $array)
                    ->get();

                $this->elasticClient->index($payload);
            } catch (Exception $exception) {
                $data = [
                    'payload' => $array,
                    'error'   => $exception->__toString()
                ];

                $this->getLogger()->error($data);
            }

            return true;
        });

        $this->updateMapping = false;
    }

    /**
     * @param Collection $models
     */
    public function bulkUpdate(Collection $models)
    {
        $model = $models->first();
        $bulkPayload = new TypePayload($model);

        $models->each(function ($model) use ($bulkPayload) {
            /** @var $model SearchableInterface | Model */
            $modelData = $model->toSearchableArray();

            if (empty($modelData)) {
                return true;
            }

            $actionPayload = (new RawPayload())
                ->set('index._id', $model->getKey());

            $bulkPayload
                ->add('body', $actionPayload->get())
                ->add('body', $modelData);
        });

        try {
            $this->elasticClient->bulk($bulkPayload->get());
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->__toString());
        }
    }

    /**
     * @return Writer
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = app()->make(Writer::class);
        }

        return $this->logger;
    }

    /**
     * @param Writer $logger
     */
    public function setLogger(Writer $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Collection $models
     */
    public function delete($models)
    {
        $models->each(function ($model) {
            $payload = (new DocumentPayload($model))->get();

            $this->elasticClient->delete($payload);
        });
    }

    /**
     * @param Builder $builder
     *
     * @return mixed
     */
    public function search(Builder $builder)
    {
        $options['limit'] = $this->limit;
        $options['page'] = $this->page;

        $res = $this->performSearch($builder, $options);

        return $res;
    }

    /**
     * @param Builder $builder
     * @param array   $options
     *
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {

        /** @var $builder SearchBuilder */
        if ($builder->callback) {

            return call_user_func(
                $builder->callback,
                $this->elasticClient,
                $builder->query,
                $options
            );
        }

        return $this->elasticClient->search($builder, $options);
    }

    /**
     * @param Builder $builder
     * @param int     $perPage
     * @param int     $page
     *
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, [
            'limit' => $perPage,
            'page'  => $page
        ]);
    }

    /**
     * @param mixed $results
     * @param Model $model
     *
     * @return Collection
     */
    public function map($results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return Collection::make([]);
        }

        if ($this->rawResult) {
            $useAll = empty($this->fields) || '*' === $this->fields || !is_array($this->fields);

            return Collection::make($results['hits']['hits'])->map(function ($hit) use ($useAll) {
                if ($useAll) {
                    return $hit['_source'];
                }

                return $this->pluckFields($hit['_source'], $this->fields);
            });
        }

        $ids = $this->mapIds($results);
        $modelKey = $model->getKeyName();

        if (is_array($this->fields)) {
            $fields = $this->fields;
            $fields[] = $modelKey;
            $fields = array_unique($fields);

            $model = $model->select($fields);
        }

        $models = $model->whereIn($modelKey, $ids)
            ->get()
            ->keyBy($modelKey);

        return Collection::make($results['hits']['hits'])
            ->map(function ($hit) use ($models) {
                $id = $hit['_id'];

                if (isset($models[$id])) {
                    return $this->appendDebugInfo($models[$id], $hit);
                }

                return [];
            })->filter()->values();
    }

    /**
     * @param mixed $results
     *
     * @return int
     */
    public function getTotalCount($results)
    {
        return (int)$results['hits']['total'];
    }

    /**
     * @param array $values
     * @param array $fields
     *
     * @return array
     */
    private function pluckFields(array $values, array $fields)
    {
        $array = [];
        $res = array_only(array_dot($values), $fields);

        foreach ($res as $key => $value) {
            array_set($array, $key, $value);
        }

        return $array;
    }

    /**
     * @param mixed $results
     *
     * @return array
     */
    public function mapIds($results)
    {
        return array_pluck($results['hits']['hits'], '_id');
    }

    /**
     * @param Model $model
     * @param array $hit
     *
     * @return Model
     */
    private function appendDebugInfo(Model $model, array $hit)
    {
        $debug = array_get($hit, '_explanation', []);

        if ($debug) {
            $model->makeVisible('debug');
            $model->debug = $debug;
        }

        return $model;
    }

    /**
     * @param bool $options
     *
     * @return $this
     */
    public function explain(bool $options = true)
    {
        $this->elasticClient->debug($options);

        return $this;
    }

    /**
     * @param bool $options
     *
     * @return $this
     */
    public function profile(bool $options = true)
    {
        $this->elasticClient->profile($options);

        return $this;
    }

    /**
     * @param Model $model
     * @param       $query
     *
     * @return mixed
     */
    public function searchRaw(Model $model, $query)
    {
        return $this->elasticClient->searchRaw($model, $query);
    }

    /**
     * @param mixed $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param bool $rawResult
     *
     * @return $this
     */
    public function setRawResult(bool $rawResult)
    {
        $this->rawResult = $rawResult;

        return $this;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }
}
