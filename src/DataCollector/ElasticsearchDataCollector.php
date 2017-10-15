<?php

namespace SynergyScoutElastic\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use SynergyScoutElastic\Client\ClientInterface;

class ElasticsearchDataCollector extends DataCollector implements DataCollectorInterface, Renderable, AssetProvider
{
    /**
     * @var ClientInterface
     */
    private $elasticClient;

    /**
     * ElasticsearchDataCollector constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->elasticClient = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $result = $this->elasticClient->getSearchQueries();

        return array_filter([
            'query' => json_encode($result),
            'count' => count($result),
        ]);
    }

    public function getWidgets()
    {
        $name = $this->getName();
        $widgets = [
            $name         => [
                "icon"    => "search",
                "widget"  => "PhpDebugBar.Widgets.RenderJsonWidget",
                "map"     => "{$name}.query",
                "default" => "{}"
            ],
            "$name:badge" => [
                "map"     => "$name.count",
                "default" => "null"
            ]
        ];

        return $widgets;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Elastic Search';
    }

    function getAssets()
    {
        return [
            'base_path' => __DIR__.'/../Resources/',
            'css'       => [
                'stylesheet.css',
                'json-widget.css',
            ],
            'js'        => [
                'renderjson.js',
                'json-widget.js',
            ]
        ];
    }
}
