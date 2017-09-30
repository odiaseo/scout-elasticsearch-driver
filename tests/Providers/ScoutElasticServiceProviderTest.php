<?php

namespace SynergyScoutElastic\Providers;

use SynergyScoutElastic\Client\ScoutElasticClient;
use SynergyScoutElastic\TestCase;

class ScoutElasticServiceProviderTest extends TestCase
{
    public function testThatScoutElasticSercviceCanBeInstantiated()
    {
        $provider = new ScoutElasticServiceProvider(app());
        $provider->register();
        $this->assertContains(ScoutElasticClient::class, $provider->provides());
    }
}
