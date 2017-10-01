<?php

namespace SynergyScoutElastic\Payloads;

use SynergyScoutElastic\Stubs\ExampleModel;
use SynergyScoutElastic\TestCase;

class PayloadTest extends TestCase
{

    /**
     * @expectedException \SynergyScoutElastic\Exception\InvalidModelException
     * @expectedExceptionCode 432
     */
    public function testExceptionIsThrownWithInvalidModelInstanceOnTypePayload()
    {
        $model = new ExampleModel();

        new TypePayload($model);
    }

    /**
     * @expectedException \SynergyScoutElastic\Exception\InvalidModelException
     * @expectedExceptionCode 431
     */
    public function testExceptionIsThrownWithInvalidModelInstanceDocumentPayload()
    {
        $model = new ExampleModel();
        new DocumentPayload($model);
    }
}