<?php

namespace SynergyScoutElastic\Facades;

use Illuminate\Support\Facades\Facade;
use SynergyScoutElastic\Client\ClientInterface;

class ElasticClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClientInterface::class;
    }
}