<?php

namespace SynergyScoutElastic\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class SearchableModelMakeCommand extends ModelMakeCommand
{
    protected $name = 'make:searchable-model';

    protected $description = 'Create a new searchable model';

    public function getStub()
    {
        return __DIR__.'/stubs/searchable_model.stub';
    }

    protected function getOptions()
    {
        $options = parent::getOptions();

        $options[] = ['index-configurator', 'i', InputOption::VALUE_REQUIRED,
            'Specify the index configurator for the model. It\'ll be created if doesn\'t exist.'];

        $options[] = ['strategy', 's', InputOption::VALUE_REQUIRED,
            'Specify the search strategy for the model. It\'ll be created if doesn\'t exist.'];

        return $options;
    }

    protected function getIndexConfigurator()
    {
        return trim($this->option('index-configurator'));
    }

    protected function getStrategy()
    {
        return trim($this->option('strategy'));
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $indexConfigurator = $this->getIndexConfigurator();
        $stub = str_replace('DummyIndexConfigurator', $indexConfigurator ? "{$indexConfigurator}::class" : 'null', $stub);

        $strategy = $this->getStrategy();
        $stub = str_replace('DummySearchStrategy', $strategy ? "{$strategy}::class" : '//', $stub);

        return $stub;
    }

    public function handle()
    {
        $indexConfigurator = $this->getIndexConfigurator();

        if ($indexConfigurator && !$this->alreadyExists($indexConfigurator)) {
            $this->call('make:index-configurator', [
                'name' => $indexConfigurator
            ]);
        }


        $searchRule = $this->getStrategy();

        if ($searchRule && !$this->alreadyExists($searchRule)) {
            $this->call('make:search-strategy', [
                'name' => $searchRule
            ]);
        }

        
        parent::handle();
    }
}
