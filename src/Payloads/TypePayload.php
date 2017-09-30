<?php

namespace SynergyScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;
use SynergyScoutElastic\Models\Searchable;
use SynergyScoutElastic\Models\SearchableInterface;

class TypePayload extends IndexPayload
{
    protected $protectedKeys = [
        'index',
        'type'
    ];

    public function __construct(Model $model)
    {
        if (!$model instanceof SearchableInterface) {
            throw new Exception(sprintf(
                'The %s model must implement %s and use the %s trait.',
                get_class($model),
                SearchableInterface::class,
                Searchable::class
            ));
        }

        parent::__construct($model->getIndexConfigurator());

        $this->payload['type'] = $model->searchableAs();
    }
}