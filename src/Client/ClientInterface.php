<?php

namespace SynergyScoutElastic\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SynergyScoutElastic\Builders\SearchBuilder;

interface ClientInterface
{

    public function delete(array $payload);

    public function index(array $payload);

    public function search(SearchBuilder $builder, array $options);

    public function buildSearchQueryPayloadCollection(SearchBuilder $builder, array $options): Collection;

    public function searchRaw(Model $model, $query);

    public function indices();

    public function debug(bool $flag);

    public function profile(bool $flag);

    public function buildTypePayload(Model $model, array $query): array;

    public function getSearchQueries(): array;
}
