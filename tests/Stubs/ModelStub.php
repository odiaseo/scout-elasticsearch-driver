<?php

namespace SynergyScoutElastic\Stubs;

use Illuminate\Database\Eloquent\Model;
use SynergyScoutElastic\Models\Searchable;
use SynergyScoutElastic\Models\SearchableInterface;

class ModelStub extends Model implements SearchableInterface
{
    use Searchable;

    protected $table = 'test_table';

    protected $primaryKey = 'id';

    protected $indexConfigurator = IndexConfiguratorStub::class;

    protected $searchStrategies = [];

    protected $mapping = [
        'properties' => [
            'id'         => [
                'type'  => 'integer',
                'index' => 'not_analyzed',
            ],
            'test_field' => [
                'type'     => 'string',
                'analyzer' => 'standard'
            ]
        ]
    ];
}