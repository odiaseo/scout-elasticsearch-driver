<?php

namespace SynergyScoutElastic\Models;

interface SearchableInterface
{
    public static function bootSearchable();

    public function getIndexConfigurator();

    public function getMapping();

    public function getSearchRules();

    public static function search($query, $callback = null);

    public static function searchRaw($query);

    public function searchableAs();

    public function toSearchableArray();
}
