<?php

namespace SynergyScoutElastic\Payloads;

use SynergyScoutElastic\IndexConfigurator;

class IndexPayload extends RawPayload
{
    protected $payload = [];

    protected $protectedKeys = [
        'index'
    ];

    public function __construct(IndexConfigurator $indexConfigurator)
    {
        $this->payload = [
            'index' => $indexConfigurator->getName()
        ];
    }

    public function setIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    public function set($key, $value)
    {
        if (!is_null($key) && !in_array($key, $this->protectedKeys)) {
            array_set($this->payload, $key, $value);
        }

        return $this;
    }

    public function get($key = null)
    {
        return array_get($this->payload, $key);
    }
}