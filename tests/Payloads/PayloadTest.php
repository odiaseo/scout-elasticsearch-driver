<?php

namespace SynergyScoutElastic\Payloads;

use SynergyScoutElastic\Exception\InvalidModelException;
use SynergyScoutElastic\Stubs\ExampleModel;
use SynergyScoutElastic\TestCase;

class PayloadTest extends TestCase
{
    public function testExceptionIsThrownWithInvalidModelInstanceOnTypePayload()
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionCode(432);

        $model = new ExampleModel();
        new TypePayload($model);
    }

    public function testExceptionIsThrownWithInvalidModelInstanceDocumentPayload()
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionCode(431);

        $model = new ExampleModel();
        new DocumentPayload($model);
    }
}