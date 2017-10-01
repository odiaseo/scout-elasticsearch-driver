<?php

namespace SynergyScoutElastic\Payloads;

use Illuminate\Database\Eloquent\Model;
use SynergyScoutElastic\Exception\InvalidModelException;

class DocumentPayload extends TypePayload
{
    protected $protectedKeys = [
        'index',
        'type',
        'id'
    ];

    public function __construct(Model $model)
    {
        if (!$model->getKey()) {
            throw new InvalidModelException($this->getInvalidModelMessage(), 431);
        }

        parent::__construct($model);

        $this->payload['id'] = $model->getKey();
    }
}
