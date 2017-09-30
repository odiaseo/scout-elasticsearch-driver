<?php

namespace SynergyScoutElastic\Stubs;

use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\Console\ElasticIndexDropCommand;

class ElasticIndexDropCommandStub extends ElasticIndexDropCommand
{

    public function __construct(ClientInterface $client, $input, $output)
    {
        parent::__construct($client);

        $this->input  = $input;
        $this->output = $output;
    }
}