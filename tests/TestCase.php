<?php

namespace SynergyScoutElastic;

use Illuminate\Config\Repository;
use Laravel\Scout\EngineManager;
use Mockery;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class TestCase extends PhpUnitTestCase
{

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        app()->instance('config', new Repository());
        app()->instance('path.config', __DIR__);
        app()->instance('scout.driver', 'elastic');
        app()->instance(EngineManager::class, new EngineManager(app()));

        parent::setUp();
    }
}