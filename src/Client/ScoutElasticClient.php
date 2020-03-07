<?php

namespace SynergyScoutElastic\Client;

use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use stdClass;
use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\Payloads\TypePayload;
use SynergyScoutElastic\Strategies\StrategyInterface;

/**
 * Class ScoutElasticClient
 *
 * @package SynergyScoutElastic\Client
 */
class ScoutElasticClient implements ClientInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var bool
     */
    private $explain = false;

    /**
     * @var bool
     */
    private $profile = false;

    /**
     * @var array
     */
    private $searchQueries = [];

    /**
     * ScoutElasticClient constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function delete(array $payload)
    {
        return $this->client->delete($payload);
    }

    /**
     * @inheritdoc
     */
    public function index(array $payload)
    {
        return $this->client->index($payload);
    }

    /**
     * @inheritdoc
     */
    public function bulk(array $payload)
    {
        return $this->client->bulk($payload);
    }

    /**
     * @inheritdoc
     */
    public function search(SearchBuilder $builder, array $options)
    {
        $result = null;
        $this->buildSearchQueryPayloadCollection($builder, $options)
            ->each(function ($payload) use (&$result) {
                $result = $this->client->search($payload);

                if ($result['hits']['total'] > 0) {
                    return false;
                }

                return true;
            });

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function buildSearchQueryPayloadCollection(SearchBuilder $builder, array $options = []): Collection
    {
        $options = array_merge($options, ['explain' => $this->explain, 'profile' => $this->profile]);
        $searchRules = $builder->getStrategies() ?: $builder->model->getSearchStrategies();

        if ($searchRules) {
            $payloadCollection = $this->buildPayloadWithRules($searchRules, $builder, $options);
        } else {
            $payloadCollection = $this->buildDefaultPayload($builder, $options);
        }

        return $payloadCollection;
    }

    /**
     * @param array         $searchRules
     * @param SearchBuilder $builder
     * @param array         $options
     *
     * @return \Illuminate\Support\Collection
     */
    private function buildPayloadWithRules(array $searchRules, SearchBuilder $builder, array $options)
    {
        $payloadCollection = collect();

        foreach ($searchRules as $index => $rule) {
            $wrap = true;
            if (is_callable($rule)) {
                $queryPayload = call_user_func($rule, $builder);
                $name = 'callable-'.$index;
            } else {
                /** @var StrategyInterface $strategy */
                $strategy = new $rule($builder);
                $wrap = $strategy->shouldWrap();
                $name = get_class($strategy);

                if ($strategy->isApplicable()) {
                    $queryPayload = $strategy->buildQueryPayload();
                } else {
                    continue;
                }
            }

            $payload = $this->buildSearchQueryPayload($builder, $queryPayload, $options, $wrap);
            $this->searchQueries[$name] = Arr::get($payload, 'body');

            $payloadCollection->push($payload);
        }

        return $payloadCollection;
    }

    /**
     * @param SearchBuilder $builder
     * @param               $queryPayload
     * @param array         $options
     *
     * @return mixed
     */
    private function buildSearchQueryPayload(SearchBuilder $builder, $queryPayload, array $options = [], $wrap = true)
    {
        foreach ($builder->wheres as $clause => $filters) {
            if (count($filters) == 0) {
                continue;
            }

            if (!Arr::has($queryPayload, 'filter.bool.'.$clause)) {
                Arr::set($queryPayload, 'filter.bool.'.$clause, []);
            }

            $queryPayload['filter']['bool'][$clause] = array_merge(
                $queryPayload['filter']['bool'][$clause],
                $filters
            );
        }

        $payload = (new TypePayload($builder->model));

        if ($wrap) {
            if(count($queryPayload) === 1 && Arr::has($queryPayload, 'filter')){
                $payload->setIfNotEmpty('body.query.constant_score', $queryPayload);
            }else{
                $payload->setIfNotEmpty('body.query.bool', $queryPayload);
            }
        } else {
            $payload->setIfNotEmpty('body', $queryPayload);
        }

        $payload->setIfNotEmpty('body.sort', $builder->orders)
            ->setIfNotEmpty('body.explain', $options['explain'] ?? null)
            ->setIfNotEmpty('body.profile', $options['profile'] ?? null);

        if ($size = isset($options['limit']) ? $options['limit'] : $builder->limit) {
            $payload->set('body.size', $size);

            if (isset($options['page'])) {
                $payload->set('body.from', ($options['page'] - 1) * $size);
            }
        }

        return $payload->get();
    }

    /**
     * @param SearchBuilder $builder
     * @param array         $options
     *
     * @return \Illuminate\Support\Collection
     */
    private function buildDefaultPayload(SearchBuilder $builder, array $options)
    {
        $payloadCollection = collect();
        $payload = $this->buildSearchQueryPayload(
            $builder,
            ['must' => ['match_all' => new stdClass()]],
            $options
        );

        $payloadCollection->push($payload);

        return $payloadCollection;
    }

    /**
     * @inheritdoc
     */
    public function indices()
    {
        return $this->client->indices();
    }

    /**
     * @inheritdoc
     */
    public function searchRaw(Model $model, $query)
    {
        return $this->client->search($this->buildTypePayload($model, $query));
    }

    /**
     * @inheritdoc
     */
    public function buildTypePayload(Model $model, array $query): array
    {
        return (new TypePayload($model))
            ->setIfNotEmpty('body', $query)
            ->get();
    }

    /**
     * @param bool $explain
     *
     * @return $this
     */
    public function debug(bool $explain)
    {
        $this->explain = $explain;

        return $this;
    }

    /**
     * @param bool $profile
     *
     * @return $this
     */
    public function profile(bool $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchQueries(): array
    {
        return $this->searchQueries;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }
}
