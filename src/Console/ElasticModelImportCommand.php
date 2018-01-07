<?php

namespace SynergyScoutElastic\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Events\ModelsImported;
use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\IndexConfigurator;
use SynergyScoutElastic\Models\Searchable;
use SynergyScoutElastic\Models\SearchableInterface;

class ElasticModelImportCommand extends ElasticIndexCreateCommand
{

    protected $signature = 'search:model-import
    {model : The model class}
    {--name= : Name of elastic search index}
    {--alias= : Name of alias to associate with the index, previous linked aliases will be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the given model into the search index';

    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * ElasticModelImportCommand constructor.
     *
     * @param ClientInterface $client
     * @param Dispatcher      $events
     */
    public function __construct(ClientInterface $client, Dispatcher $events)
    {
        parent::__construct($client);

        $this->events = $events;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$model = $this->getModel()) {
            return;
        }

        set_time_limit(0);

        $start = time();
        $class = get_class($model);
        $indexName = $model->getIndexConfigurator()->getName();

        $this->info('Importing [' . $class . '] into index [' . $indexName . ']');

        $this->events->listen(ModelsImported::class, function ($event) use ($class) {
            $key = $event->models->last()->getKey();

            $this->line('<comment>Imported [' . $class . '] models up to ID:</comment> ' . $key);
        });

        $model::makeAllSearchable();

        $this->events->forget(ModelsImported::class);

        if ($alias = (string)$this->option('alias')) {
            $this->removeLinkedIndexes($alias, $indexName);
        }

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

        $configurator = $modelInstance->getIndexConfigurator();

        if ($name = (string)$this->option('name')) {
            $configurator->setName($name);
        }

        $this->ensureIndexExists($configurator);

        return $modelInstance;
    }

    protected function ensureIndexExists(IndexConfigurator $indexConfigurator)
    {
        if (!$this->client->indices()->exists(['index' => $indexConfigurator->getName()])) {
            $this->createIndex($indexConfigurator);
        }
    }

    protected function removeLinkedIndexes(string $alias, string $activeIndex)
    {
        $this->client->indices()->getAliases(['index' => $alias]);
        $aliases = $this->client->indices()->getAliases(['index' => $alias]);

        foreach (array_keys($aliases) as $name) {
            if ($activeIndex && $activeIndex !== $name) {
                $this->client->indices()->delete(['index' => $name]);
                $this->info(sprintf(' >> Linked index[%s] deleted ', $name));
            }
        }
    }
}
