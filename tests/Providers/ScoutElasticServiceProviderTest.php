<?php

namespace SynergyScoutElastic\Providers;

use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\TestCase;

class ScoutElasticServiceProviderTest extends TestCase
{
    public function testThatScoutElasticSercviceCanBeInstantiated()
    {
        $provider = new ScoutElasticServiceProvider(app());
        $provider->register();
        $this->assertContains(ClientInterface::class, $provider->provides());
    }
}
