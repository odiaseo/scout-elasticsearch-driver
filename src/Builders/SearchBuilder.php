<?php

namespace SynergyScoutElastic\Builders;

class SearchBuilder extends FilterBuilder
{
    public $rules = [];

    public function rule($rule)
    {
        $this->rules[] = $rule;

        return $this;
    }
}