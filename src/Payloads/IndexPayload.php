<?php

namespace SynergyScoutElastic\Payloads;

use Illuminate\Support\Arr;
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
            Arr::set($this->payload, $key, $value);
        }

        return $this;
    }

    public function get($key = null)
    {
        return Arr::get($this->payload, $key);
    }
}