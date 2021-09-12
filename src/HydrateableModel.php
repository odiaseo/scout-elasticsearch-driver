<?php

namespace SynergyScoutElastic;

interface HydrateableModel
{
    public function hydrateSearchResult(array $results): array;
}