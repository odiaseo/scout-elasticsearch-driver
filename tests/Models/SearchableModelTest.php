<?php

namespace SynergyScoutElastic\Models;

use SynergyScoutElastic\Builders\SearchBuilder;
use SynergyScoutElastic\Stubs\ModelStub;
use SynergyScoutElastic\TestCase;

class SearchableModelTest extends TestCase
{

    public function testSearchableModelGeneratesQueries()
    {

        $model  = new ModelStub();
        $result = $model->search('shoe', function () {
        });

        $this->assertInstanceOf(SearchBuilder::class, $result);
    }
}