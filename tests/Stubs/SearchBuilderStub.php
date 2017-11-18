<?php

namespace SynergyScoutElastic\Stubs;

use Prophecy\Argument;
use Prophecy\Prophet;
use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\ElasticEngine;

class SearchBuilderStub extends SearchBuilder
{
    public function engine()
    {
        $engine = (new Prophet())->prophesize(ElasticEngine::class);

        $engine->search(Argument::class)->willReturn([]);

        return $engine;
    }
}
