<?php

namespace SynergyScoutElastic;

use Illuminate\Support\Str;

abstract class IndexConfigurator
{
    protected $name;

    protected $settings = [];

    protected $defaultMapping = [];

    protected $aliases = [];

    /**
     * @return string
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = $this->getDefaultIndexName();
        }

        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDefaultIndexName()
    {
        return Str::snake(str_replace('IndexConfigurator', '', class_basename($this)));
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function getDefaultMapping()
    {
        return $this->defaultMapping;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
}