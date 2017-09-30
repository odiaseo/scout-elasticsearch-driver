<?php

namespace SynergyScoutElastic;

use Mockery;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use SynergyScoutElastic\Facades\ElasticClient;

class TestCase extends PhpUnitTestCase
{

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp()
    {
        app()->instance('config', new class()
        {
            public function get($key)
            {
                return '';
            }
        });

        parent::setUp();
    }

    protected function mockClient()
    {
        return Mockery::mock('alias:' . ElasticClient::class);
    }
}