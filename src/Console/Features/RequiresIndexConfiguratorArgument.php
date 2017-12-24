<?php

namespace SynergyScoutElastic\Console\Features;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use SynergyScoutElastic\IndexConfigurator;

trait RequiresIndexConfiguratorArgument
{
    /**
     *
     * @return IndexConfigurator
     */
    protected function getIndexConfigurator()
    {
        $name = (string)$this->option('name');
        $configuratorClass = trim($this->argument('index-configurator'));
        $name = trim($name);
        $configuratorInstance = new $configuratorClass($name);

        if (!($configuratorInstance instanceof IndexConfigurator)) {
            $this->error(sprintf(
                'The class %s must extend %s.',
                $configuratorClass,
                IndexConfigurator::class
            ));

            return null;
        }

        return $configuratorInstance;
    }

    protected function getArguments()
    {
        return [
            ['index-configurator', InputArgument::REQUIRED, 'The index configurator class'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_OPTIONAL, 'Name of index to create'],
        ];
    }
}