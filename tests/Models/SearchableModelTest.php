<?php
namespace SynergyScoutElastic\Models;


use Laravel\Scout\Events\ModelsImported;
use SynergyScoutElastic\Stubs\ModelStub;
use SynergyScoutElastic\TestCase;

class SearchableModelTest extends TestCase {

    public function testSearchableModelGeneratesQueries(){

        $model = new ModelStub();
        $model->search('shoe', function(){});
    }
}