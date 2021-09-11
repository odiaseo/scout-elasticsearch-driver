<?php

namespace SynergyScoutElastic\Builders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use SynergyScoutElastic\Stubs\ModelStub;
use SynergyScoutElastic\TestCase;

class SearchBuilderTest extends TestCase
{
    /**
     * @dataProvider  whereOperatorProvider
     *
     * @param $method
     * @param $field
     * @param $value
     * @param $expectedKeys
     */
    public function testThatWhereOperatorsAreResolvedCorrectly($method, $field, $value, $expectedKeys)
    {

        try {

            /** @var SearchBuilder $builder */
            $builder = new SearchBuilder(new ModelStub(), '');

            $this->assertEquals(0, count($builder->wheres['must']));
            $this->assertEquals(0, count($builder->wheres['must_not']));

            $data = $builder->$method($field, $value);
            $group = [];
            $clause = [];

            foreach ($data->wheres as $key => $group) {
                if (count($group)) {
                    $clause[$key] = $group;
                    break;
                }
            }

            $this->assertEquals(1, count($group));
            $json = json_encode($clause);

            foreach ((array)$expectedKeys as $param) {
                $this->assertSame($param, $json);
            }
        } catch (\Throwable $exception) {
            $this->assertStringContainsString("A facade root has not been set", $exception->getMessage());
        }
    }

    /**
     * @dataProvider  whereOperatorProvider
     */
    public function testSearchableModelGeneratesQueries($method, $field, $value)
    {

        $model = new ModelStub();
        $result = $model->search(
            'shoe',
            function () {
            })->$method($field, $value)
            ->get();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function whereOperatorProvider()
    {
        return [
            ['where', 'id', 1, '{"must":[{"match":{"id":1}}]}'],
            ['whereIn', 'id', [1, 2, 3], '{"must":[{"terms":{"id":[1,2,3]}}]}'],
            ['whereNotIn', 'id', [2, 4, 6], '{"must_not":[{"terms":{"id":[2,4,6]}}]}'],
            ['whereBetween', 'id', [1, 10], '{"must":[{"range":{"id":{"gte":1,"lte":10}}}]}'],
            ['whereNotBetween', 'id', [10, 20], '{"must_not":[{"range":{"id":{"gte":10,"lte":20}}}]}'],
            ['whereExists', 'id', '', '{"must":[{"exists":{"field":"id"}}]}'],
            ['whereNotExists', 'id', '', '{"must_not":[{"exists":{"field":"id"}}]}'],
            ['whereRegexp', 'id', '^home&', '{"must":[{"regexp":{"id":{"value":"^home&","flags":"ALL"}}}]}'],
        ];
    }
}