<?php

namespace SynergyScoutElastic\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Events\ModelsImported;
use SynergyScoutElastic\Models\Searchable;
use SynergyScoutElastic\Models\SearchableInterface;

class ElasticModelImportCommand extends BaseCommand
{

    protected $signature = 'search:model-import
    {model : The model class}
    {name : Name of elastic search index}';

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

    /**
     * @return SearchableInterface | Model
     */
    protected function getModel()
    {
        $modelClass = trim($this->argument('model'));

        $modelInstance = new $modelClass;

        if (!($modelInstance instanceof Model) || !$modelInstance instanceof SearchableInterface) {
            $this->error(sprintf(
                'The %s class must extend %s, implement %s and use the %s trait.',
                $modelClass,
                Model::class,
                SearchableInterface::class,
                Searchable::class
            ));

            return null;
        }

        $name = (string)$this->argument('name');
        $modelInstance->getIndexConfigurator()->setName($name);

        return $modelInstance;
    }
}
