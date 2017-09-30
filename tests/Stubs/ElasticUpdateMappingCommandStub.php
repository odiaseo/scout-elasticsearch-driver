<?php

namespace SynergyScoutElastic\Stubs;

use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\Console\ElasticIndexUpdateCommand;
use SynergyScoutElastic\Console\ElasticUpdateMappingCommand;

class ElasticUpdateMappingCommandStub extends ElasticUpdateMappingCommand
{

    public function __construct(ClientInterface $client, $input, $output)
    {
        parent::__construct($client);

        $this->input  = $input;
        $this->output = $output;
    }
}