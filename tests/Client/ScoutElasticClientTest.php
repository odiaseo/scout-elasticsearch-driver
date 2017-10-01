<?php

namespace SynergyScoutElastic\Client;

use Elasticsearch\Client;
use Prophecy\Argument;
use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\ElasticEngineTest;
use SynergyScoutElastic\Strategies\FindAllStrategy;
use SynergyScoutElastic\Stubs\ModelStub;
use SynergyScoutElastic\TestCase;

/**
 * Class ScoutElasticClient
 *
 * @package SynergyScoutElastic\Client
 */
class ScoutElasticClientTest extends TestCase
{
    /**
     * @var ScoutElasticClient
     */
    private $client;

    public function setUp()
    {
        parent::setUp();

        $elastic = $this->prophesize(Client::class);
        $elastic->index(Argument::any())->willReturn([]);
        $elastic->delete(Argument::any())->willReturn([]);
        $elastic->search(Argument::any())->willReturn(ElasticEngineTest::getElasticSearchResponse());

        $this->client = new ScoutElasticClient($elastic->reveal());
    }

    public function testClientIndexReturnsCorrectResponse()
    {
        $this->client->debug(true)
            ->profile(false);

        $this->assertSame(0, count($this->client->getSearchQueries()));
        $this->assertInternalType('array', $this->client->index([]));
    }

    public function testClientDeleteReturnsCorrectResponse()
    {
        $this->assertInternalType('array', $this->client->delete([]));
    }

    public function testClientSearchReturnsCorrectResponse()
    {
        $options = [
            'profile' => true
        ];
        $builder = new SearchBuilder(new ModelStub(), 'shoe');
        $result  = $this->client->search($builder, $options);
        $this->assertInternalType('array', $result);
    }

    public function testClientSearchRawReturnsCorrectResponse()
    {
        $builder = new SearchBuilder(new ModelStub(), 'shoe');
        $result  = $this->client->searchRaw(new ModelStub(), (new FindAllStrategy($builder))->buildQueryPayload());
        $this->assertInternalType('array', $result);
    }
}
