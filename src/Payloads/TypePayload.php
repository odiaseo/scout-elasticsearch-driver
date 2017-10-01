<?php

namespace SynergyScoutElastic\Payloads;

use Illuminate\Database\Eloquent\Model;
use SynergyScoutElastic\Exception\InvalidModelException;
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
            throw new InvalidModelException($this->getInvalidModelMessage(), 432);
        }

        parent::__construct($model->getIndexConfigurator());

        $this->payload['type'] = $model->searchableAs();
    }

    /**
     * @return string
     */
    protected function getInvalidModelMessage()
    {
        return sprintf(
            'The %s model must implement %s and use the %s trait.',
            get_class($this),
            SearchableInterface::class,
            Searchable::class
        );
    }
}