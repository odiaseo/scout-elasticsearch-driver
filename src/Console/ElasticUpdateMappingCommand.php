<?php

namespace SynergyScoutElastic\Console;

use SynergyScoutElastic\Console\Features\RequiresModelArgument;
use SynergyScoutElastic\Payloads\TypePayload;

class ElasticUpdateMappingCommand extends BaseCommand
{
    use RequiresModelArgument;

    protected $name = 'search:update-mapping';

    protected $description = 'Update model elasticsearch mapping';

    public function handle()
    {
        if (!$model = $this->getModel()) {
            return;
        }

        $configurator = $model->getIndexConfigurator();
        $mapping = array_merge_recursive($configurator->getDefaultMapping(), $model->getMapping());

        if (empty($mapping)) {
            $this->error('Nothing to update: the mapping is not specified.');

            return;
        }

        $payload = (new TypePayload($model))
            ->set('body.' . $model->searchableAs(), $mapping)
            ->get();

        $this->client->indices()
            ->putMapping($payload);

        $this->info(sprintf(
            'The %s mapping was updated!',
            $model->searchableAs()
        ));
    }
}