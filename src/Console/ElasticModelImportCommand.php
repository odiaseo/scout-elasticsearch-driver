<?php

namespace SynergyScoutElastic\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Scout\Events\ModelsImported;
use SynergyScoutElastic\Console\Features\RequiresModelArgument;

class ElasticModelImportCommand extends BaseCommand
{
    use RequiresModelArgument;

    protected $name = 'search:model-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the given model into the search index';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function handle(Dispatcher $events)
    {
        if (!$model = $this->getModel()) {
            return;
        }

        $start = time();
        $class = get_class($model);
        $this->info('Importing [' . $class . '] into index [.' . $model->getIndexConfigurator()->getName() . ']');

        $events->listen(ModelsImported::class, function ($event) use ($class) {
            $key = $event->models->last()->getKey();

            $this->line('<comment>Imported [' . $class . '] models up to ID:</comment> ' . $key);
        });

        $model::makeAllSearchable();

        $events->forget(ModelsImported::class);
        $end = time();
        $duration = round(($end - $start) / 60, 1);
        $this->info(sprintf('All [%s] records have been imported in %s mins.', $class, $duration));
    }
}
