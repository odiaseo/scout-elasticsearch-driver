<?php

namespace SynergyScoutElastic\Console;

use Illuminate\Console\Command;
use SynergyScoutElastic\Client\ClientInterface;

abstract class BaseCommand extends Command
{

    protected $client;

    public function __construct(ClientInterface $client)
    {
        parent::__construct();

        $this->client = $client;
    }
}
