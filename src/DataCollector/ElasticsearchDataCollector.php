<?php

namespace SynergyScoutElastic\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use SynergyScoutElastic\Client\ClientInterface;

class ElasticsearchDataCollector extends DataCollector implements DataCollectorInterface, Renderable
{
    /**
     * @var ClientInterface
     */
    private $elasticClient;

    /**
     * ElasticsearchDataCollector constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->elasticClient = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'elastic-search';
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {

        $result = $this->elasticClient->getSearchQueries();

        return array_filter([
            'query' => json_encode($result),
        ]);
    }

    public function getWidgets()
    {
        $widgets = [
            "Elastic Search" => [
                "icon"    => "search",
                "widget"  => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                "map"     => "elastic-search",
                "default" => "{}"
            ]
        ];

        return $widgets;
    }
}
