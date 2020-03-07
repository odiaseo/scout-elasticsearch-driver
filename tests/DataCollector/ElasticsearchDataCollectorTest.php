<?php

namespace SynergyScoutElastic\DataCollector;

use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\TestCase;

class ElasticsearchDataCollectorTest extends TestCase
{
    /**
     * @var ElasticsearchDataCollector
     */
    private $collector;

    public function setUp(): void
    {
        parent::setUp();

        $client = $this->prophesize(ClientInterface::class);
        $client->getSearchQueries()->willReturn(['test' => 1]);
        $this->collector = new ElasticsearchDataCollector($client->reveal());
    }

    public function testCollectorName()
    {
        $this->assertSame('Elastic Search', $this->collector->getName());
    }

    public function testCollectedDataContainsRequiredKeys()
    {
        $message = $this->collector->collect();
        $this->assertArrayHasKey('query', $message);
        $this->assertSame('{"test":1}', $message['query']);
    }

    public function testThanDisplayWidgetDataContainsTheRequiredKeys()
    {
        $widget = $this->collector->getWidgets();
        $this->assertArrayHasKey('Elastic Search', $widget);
    }
}
