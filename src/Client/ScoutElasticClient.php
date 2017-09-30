<?php

namespace SynergyScoutElastic\Client;

use Elasticsearch\Client;

/**
 * Class ScoutElasticClient
 * @package SynergyScoutElastic\Client
 */
class ScoutElasticClient implements ClientInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * ScoutElasticClient constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    /**
     * @inheritdoc
     */
    public function delete(array $payload){
        return $this->client->delete($payload);
    }
    /**
     * @inheritdoc
     */
    public function index(array $payload){
        return $this->client->index($payload);
    }
    /**
     * @inheritdoc
     */
    public function search(array $payload){
        return $this->client->search($payload);
    }

    /**
     * @inheritdoc
     */
    public function indices(){
        return $this->client->indices();
    }
    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }
}
