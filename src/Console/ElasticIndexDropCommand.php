<?php

namespace SynergyScoutElastic\Console;

use SynergyScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use SynergyScoutElastic\Payloads\IndexPayload;

class ElasticIndexDropCommand extends BaseCommand
{
    use RequiresIndexConfiguratorArgument;

    protected $name = 'search:drop-index';

    protected $description = 'Drop an Elasticsearch index';

    public function handle()
    {
        if (!$configurator = $this->getIndexConfigurator()) {
            return;
        }

        $payload = (new IndexPayload($configurator))
            ->get();

        $this->client->indices()
            ->delete($payload);

        $this->info(sprintf(
            'The index %s was deleted!',
            $configurator->getName()
        ));
    }
}