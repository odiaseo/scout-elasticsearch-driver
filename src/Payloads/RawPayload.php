<?php

namespace SynergyScoutElastic\Payloads;

use Illuminate\Support\Arr;

class RawPayload
{
    protected $payload = [];

    public function setIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    public function set($key, $value)
    {
        if (!is_null($key)) {
            Arr::set($this->payload, $key, $value);
        }

        return $this;
    }

    public function has($key)
    {
        return Arr::has($this->payload, $key);
    }

    public function addIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->add($key, $value);
    }

    public function add($key, $value)
    {
        if (!is_null($key)) {
            $currentValue = Arr::get($this->payload, $key, []);

            if (!is_array($currentValue)) {
                $currentValue = Arr::wrap($currentValue);
            }

            $currentValue[] = $value;

            Arr::set($this->payload, $key, $currentValue);
        }

        return $this;
    }

    public function get($key = null)
    {
        return Arr::get($this->payload, $key);
    }
}