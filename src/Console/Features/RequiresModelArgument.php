<?php

namespace SynergyScoutElastic\Console\Features;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Input\InputArgument;
use SynergyScoutElastic\Models\Searchable;
use SynergyScoutElastic\Models\SearchableInterface;

trait RequiresModelArgument
{
    protected function getModel()
    {
        $modelClass = trim($this->argument('model'));

        $modelInstance = new $modelClass;

        if (!($modelInstance instanceof Model) || !$modelInstance instanceof SearchableInterface) {
            $this->error(sprintf(
                'The %s class must extend %s, implement %s and use the %s trait.',
                $modelClass,
                Model::class,
                SearchableInterface::class,
                Searchable::class
            ));

            return null;
        }

        return $modelInstance;
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The model class'],
        ];
    }
}