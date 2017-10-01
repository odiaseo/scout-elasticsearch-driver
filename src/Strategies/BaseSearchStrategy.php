<?php

namespace SynergyScoutElastic\Strategies;

use SynergyScoutElastic\Builders\SearchBuilder;

abstract class BaseSearchStrategy implements StrategyInterface
{
    /**
     * @var SearchBuilder
     */
    protected $builder;

    /**
     * @param SearchBuilder $builder
     */
    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return bool
     */
    public function isApplicable(): bool
    {
        return true;
    }

    /**
     * @return SearchBuilder
     */
    public function getBuilder(): SearchBuilder
    {
        return $this->builder;
    }
}
