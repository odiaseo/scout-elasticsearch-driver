<?php

namespace SynergyScoutElastic\Client;

interface ClientInterface
{

    public function delete(array $payload);

    public function index(array $payload);

    public function search(array $payload);

    public function indices();
}
