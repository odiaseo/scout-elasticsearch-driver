<?php

namespace SynergyScoutElastic\Console;

use SynergyScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use SynergyScoutElastic\Payloads\IndexPayload;

class ElasticIndexCreateCommand extends BaseCommand
{
    use RequiresIndexConfiguratorArgument;

    protected $name = 'search:create-index';

    protected $description = 'Create an Elasticsearch index';

    public function handle()
    {
        if (!$configurator = $this->getIndexConfigurator()) {
            return;
        }

        $payload = (new IndexPayload($configurator))
            ->setIfNotEmpty('body.settings', $configurator->getSettings())
            ->setIfNotEmpty('body.mappings._default_', $configurator->getDefaultMapping())
            ->get();

        $this->client->indices()->create($payload);

        $this->info(sprintf(
            'The index %s was created!',
            $configurator->getName()
        ));
    }
}