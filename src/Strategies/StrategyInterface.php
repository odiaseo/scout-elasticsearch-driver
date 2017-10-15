<?php

namespace SynergyScoutElastic\Strategies;

use SynergyScoutElastic\Builders\SearchBuilder;

interface StrategyInterface
{
    public function isApplicable(): bool;

    public function shouldWrap(): bool;

    public function buildQueryPayload(): array;

    public function getBuilder(): SearchBuilder;
}
