<?php

namespace SynergyScoutElastic\Strategies;

class FindAllStrategy extends BaseSearchStrategy
{

    /**
     * @return array
     */
    public function buildQueryPayload(): array
    {
        return [
            'must' => [
                'match' => [
                    '_all' => $this->getBuilder()->query
                ]
            ]
        ];
    }
}
