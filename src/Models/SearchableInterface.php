<?php

namespace SynergyScoutElastic\Models;

use SynergyScoutElastic\IndexConfigurator;

interface SearchableInterface
{
    public static function bootSearchable();

    public static function search($query, $callback = null);

    public static function searchRaw($query);

    /**
     * @return IndexConfigurator
     */
    public function getIndexConfigurator(): IndexConfigurator;

    /**
     * @param IndexConfigurator $configurator
     *
     * @return mixed
     */
    public function setIndexConfigurator(IndexConfigurator $configurator);

    /**
     * @return array
     */
    public function getMapping(): array;

    /**
     * @return array
     */
    public function getSearchStrategies(): array;

    /**
     * @return array
     */
    public function toSearchableArray();

    /**
     * @return string
     */
    public function searchableAs();

}
