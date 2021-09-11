<?php

namespace SynergyScoutElastic\Providers;

use DebugBar\DebugBar;
use Illuminate\Foundation\Application;
use Laravel\Scout\EngineManager;
use Prophecy\Argument;
use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\DataCollector\ElasticsearchDataCollector;
use SynergyScoutElastic\TestCase;

class ScoutElasticServiceProviderTest extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ScoutElasticServiceProvider
     */
    private $provider;

    public function setUp(): void
    {
        parent::setUp();

        config(['path.config' => __DIR__]);
        $app    = $this->prophesize(Application::class);
        $client = $this->prophesize(ClientInterface::class);

        $app->configPath('synergy-scout-elastic.php')->willReturn(true);
        $app->make('config')->willReturn(true);
        $app->make(EngineManager::class)->willReturn(new EngineManager($app->reveal()));
        $app->make(ElasticsearchDataCollector::class)->willReturn(new ElasticsearchDataCollector($client->reveal()));
        $app->make('debugbar')->willReturn(new DebugBar());
        $app->singleton(Argument::cetera())->willReturn($client->reveal());
        $app->alias(Argument::cetera())->willReturn($app);
        $app->has('debugbar')->willReturn(true);

        $this->app      = $app->reveal();
        $this->provider = new ScoutElasticServiceProvider($this->app);
        $this->provider->register();
        $this->provider->boot();

    }

    public function testThatScoutElasticSercviceCanBeInstantiated()
    {
        $this->assertContains(ClientInterface::class, $this->provider->provides());
    }
}
