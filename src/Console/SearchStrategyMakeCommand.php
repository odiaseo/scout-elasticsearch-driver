<?php

namespace SynergyScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SearchStrategyMakeCommand extends GeneratorCommand
{
    protected $name = 'make:search-strategy';

    protected $description = 'Create a new search rule';

    protected $type = 'Rule';

    public function getStub()
    {
        return __DIR__.'/stubs/search_rule.stub';
    }
}