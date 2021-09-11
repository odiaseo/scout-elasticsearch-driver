<?php

namespace SynergyScoutElastic\Console;

use SynergyScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use SynergyScoutElastic\IndexConfigurator;
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

        $this->createIndex($configurator);
    }

    protected function createIndex(IndexConfigurator $configurator)
    {
        $payload = (new IndexPayload($configurator))
            ->setIfNotEmpty('body.settings', $configurator->getSettings())
            ->setIfNotEmpty('body.mappings', $configurator->getDefaultMapping())
            ->setIfNotEmpty('body.aliases', $configurator->getAliases())
            ->get();

        $this->client->indices()->create($payload);

        $this->info(sprintf(
            'The index %s was created!',
            $configurator->getName()
        ));
    }
}