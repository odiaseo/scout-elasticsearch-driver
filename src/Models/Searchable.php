<?php

namespace SynergyScoutElastic\Models;

use Exception;
use Laravel\Scout\Searchable as ScoutSearchable;
use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\IndexConfigurator;
use SynergyScoutElastic\Strategies\FindAllStrategy;

trait Searchable
{
    use ScoutSearchable {
        ScoutSearchable::bootSearchable as bootScoutSearchable;
    }

    /**
     * @var bool
     */
    private static $isSearchableTraitBooted = false;

    public static function bootSearchable()
    {
        if (self::$isSearchableTraitBooted) {
            return;
        }

        self::bootScoutSearchable();

        self::$isSearchableTraitBooted = true;
    }

    /**
     * @param      $query
     * @param null $callback
     *
     * @return SearchBuilder
     */
    public static function search($query, $callback = null)
    {
        return new SearchBuilder(new static, $query, $callback);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public static function searchRaw($query)
    {
        $model = new static();

        return $model->searchableUsing()
            ->searchRaw($model, $query);
    }

    /**
     * @return IndexConfigurator
     * @throws Exception If an index configurator is not specified
     */
    public function getIndexConfigurator(): IndexConfigurator
    {
        static $indexConfigurator;

        if (!$indexConfigurator) {
            if (!isset($this->indexConfigurator) || empty($this->indexConfigurator)) {
                throw new Exception(sprintf('An index configurator for the %s model is not specified.', __CLASS__));
            }

            $indexConfiguratorClass = $this->indexConfigurator;
            $indexConfigurator = new $indexConfiguratorClass;
        }

        return $indexConfigurator;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return isset($this->mapping) ? $this->mapping : [];
    }

    /**
     * @return array
     */
    public function getSearchStrategies(): array
    {
        return !empty($this->searchStrategies) ? $this->searchStrategies : [FindAllStrategy::class];
    }
}
