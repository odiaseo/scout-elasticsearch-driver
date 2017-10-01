<?php

namespace SynergyScoutElastic\Providers;

use Elasticsearch\ClientBuilder;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\Client\ScoutElasticClient;
use SynergyScoutElastic\Console\ElasticIndexCreateCommand;
use SynergyScoutElastic\Console\ElasticIndexDropCommand;
use SynergyScoutElastic\Console\ElasticIndexUpdateCommand;
use SynergyScoutElastic\Console\ElasticUpdateMappingCommand;
use SynergyScoutElastic\Console\IndexConfiguratorMakeCommand;
use SynergyScoutElastic\Console\SearchableModelMakeCommand;
use SynergyScoutElastic\Console\SearchStrategyMakeCommand;
use SynergyScoutElastic\DataCollector\ElasticsearchDataCollector;
use SynergyScoutElastic\ElasticEngine;

/**
 * Class ScoutElasticServiceProvider
 * @package SynergyScoutElastic\Providers
 */
class ScoutElasticServiceProvider extends ServiceProvider
{

    protected $defer = false;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/synergy-scout-elastic.php' => config_path('synergy-scout-elastic.php'),
        ]);

        $this->commands([
            // make commands
            IndexConfiguratorMakeCommand::class,
            SearchableModelMakeCommand::class,
            SearchStrategyMakeCommand::class,

            // elastic commands
            ElasticIndexCreateCommand::class,
            ElasticIndexUpdateCommand::class,
            ElasticIndexDropCommand::class,
            ElasticUpdateMappingCommand::class,
        ]);

        $this->app->make(EngineManager::class)
            ->extend('elastic', function () {
                return $this->app->make(ElasticEngine::class);
            });
    }

    public function register()
    {
        $this->app->singleton(ClientInterface::class, function () {
            $config = (array)$this->app->make('config')->get('synergy-scout-elastic.client');

            return new ScoutElasticClient(ClientBuilder::fromConfig($config));
        });

        $this->app->singleton(ElasticEngine::class, function () {
            $updateMapping = (bool)$this->app->make('config')->get('scout_elastic.update_mapping');

            $kernel        = $this->app->make(Kernel::class);
            $elasticClient = $this->app->make(ClientInterface::class);

            return new ElasticEngine($kernel, $elasticClient, $updateMapping);
        });

        $this->app->alias(ClientInterface::class, ScoutElasticClient::class);
        $this->addElasticDataCollector();

    }

    private function addElasticDataCollector()
    {
        if ($this->app->has('debugbar')) {
            $debugbar = $this->app->make('debugbar');
            $debugbar->addCollector($this->app->make(ElasticsearchDataCollector::class));
        }
    }

    public function provides()
    {
        return [
            ClientInterface::class,
            ElasticEngine::class,
        ];
    }
}