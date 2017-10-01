<?php

namespace SynergyScoutElastic;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\Models\SearchableInterface;
use SynergyScoutElastic\Payloads\DocumentPayload;

class ElasticEngine extends Engine
{
    /**
     * @var bool
     */
    protected $updateMapping = false;

    /**
     * @var
     */
    protected $query;

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
     * @param Collection $models
     */
    public function update($models)
    {
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

            $payload = (new DocumentPayload($model))
                ->set('body', $array)
                ->get();

            $this->elasticClient->index($payload);

            return true;
        });

        $this->updateMapping = false;
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
        $res = $this->performSearch($builder);

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
            $this->query = $builder->query;

            return $this->result = call_user_func(
                $builder->callback,
                $this->elasticClient,
                $builder->query,
                $options
            );
        }

        return $this->elasticClient->search($builder, $options);
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
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
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
        if ($this->getTotalCount($results) == 0) {
            return Collection::make();
        }

        $ids = $this->mapIds($results);
        $modelKey = $model->getKeyName();
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
            })->filter();
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
     * @param mixed $results
     *
     * @return array
     */
    public function mapIds($results)
    {
        return array_pluck($results['hits']['hits'], '_id');
    }

    private function appendDebugInfo(Model $model, array $hit)
    {
        $debug = array_get($hit, '_explanation', []);

        if ($debug) {
            $model->makeVisible('debug');
            $model->debug = $debug;
        }

        return $model;
    }
}
