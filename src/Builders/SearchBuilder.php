<?php

namespace SynergyScoutElastic\Builders;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use SynergyScoutElastic\Models\SearchableInterface;

/**
 * Class SearchBuilder
 *
 * @property Model | SearchableInterface $model
 * @package SynergyScoutElastic\Builders]
 */
class SearchBuilder extends Builder
{
    public $wheres = [
        'must'     => [],
        'should'   => [],
        'must_not' => []
    ];

    /**
     * @var array
     */
    private $strategies = [];

    /**
     * @var array
     */
    private $compare = [
        '!=',
        '<>',
        '>=',
        '<=',
        '>',
        '<',
        '=',
    ];

    /**
     * @param mixed $fields
     *
     * @return $this
     */
    public function select($fields)
    {
        $this->engine()->setFields($fields);

        return $this;
    }

    public function rawResult(bool $value)
    {
        $this->engine()->setRawResult($value);

        return $this;
    }

    /**
     * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
     *
     * @param string $field Field name
     * @param mixed  $value Scalar value or an array
     *
     * @return $this
     */
    public function where($field, $value)
    {
        $this->addClause($field, $value);

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     * @param string $boolOperator
     *
     * @return $this
     */
    private function addClause(string $field, $value, $boolOperator = 'must')
    {
        $args = func_get_args();

        if (count($args) == 3) {
            list($field, $operator, $value) = $args;
        } else {
            $operator = '=';
            foreach ($this->compare as $op) {
                if (ends_with($value, $op)) {
                    $operator = $op;
                    break;
                }
            }
        }

        if (starts_with($field, '-')) {
            $boolOperator = 'must_not';
        }

        $field = trim($field, '-+');
        if (is_string($value)) {
            $value = str_replace($this->compare, '', $value);
        }

        if (is_numeric($value)) {
            $value = $value * 1;
        }

        switch ($operator) {
            case '=':
                $this->wheres[$boolOperator][] = ['term' => [$field => $value]];
                break;

            case '>':
                $this->wheres[$boolOperator][] = ['range' => [$field => ['gt' => $value]]];
                break;

            case '<';
                $this->wheres[$boolOperator][] = ['range' => [$field => ['lt' => $value]]];
                break;

            case '>=':
                $this->wheres[$boolOperator][] = ['range' => [$field => ['gte' => $value]]];
                break;

            case '<=':
                $this->wheres[$boolOperator][] = ['range' => [$field => ['lte' => $value]]];
                break;

            case '!=':
            case '<>':
                $this->wheres[$boolOperator][] = ['term' => [$field => $value]];
                break;
        }

        return $this;
    }

    public function orWhere($field, $value)
    {
        $this->addClause($field, $value, 'should');

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->orders[] = [$column => strtolower($direction) == 'asc' ? 'asc' : 'desc'];

        return $this;
    }

    /**
     * @param       $field
     * @param array $value
     *
     * @return $this
     */
    public function whereIn($field, array $value)
    {
        $this->wheres['must'][] = ['terms' => [$field => $value]];

        return $this;
    }

    /**
     * @param       $field
     * @param array $value
     *
     * @return $this
     */
    public function whereNotIn($field, array $value)
    {
        $this->wheres['must_not'][] = ['terms' => [$field => $value]];

        return $this;
    }

    /**
     * @param       $field
     * @param array $value
     *
     * @return $this
     */
    public function whereBetween($field, array $value)
    {
        $this->wheres['must'][] = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];

        return $this;
    }

    /**
     * @param       $field
     * @param array $value
     *
     * @return $this
     */
    public function whereNotBetween($field, array $value)
    {
        $this->wheres['must_not'][] = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];

        return $this;
    }

    /**
     * @param $field
     *
     * @return $this
     */
    public function whereExists($field)
    {
        $this->wheres['must'][] = ['exists' => ['field' => $field]];

        return $this;
    }

    /**
     * @param $field
     *
     * @return $this
     */
    public function whereNotExists($field)
    {
        $this->wheres['must_not'][] = ['exists' => ['field' => $field]];

        return $this;
    }

    /**
     * @param        $field
     * @param        $value
     * @param string $flags
     *
     * @return $this
     */
    public function whereRegexp($field, $value, $flags = 'ALL')
    {
        $this->wheres['must'][] = ['regexp' => [$field => ['value' => $value, 'flags' => $flags]]];

        return $this;
    }

    /**
     * @param bool $option
     *
     * @return $this
     */
    public function explain($option = true)
    {
        $this->engine()->explain($option);

        return $this;
    }

    /**
     * @param bool $option
     *
     * @return $this
     */
    public function profile($option = true)
    {
        $this->engine()->profile($option);

        return $this;
    }

    /**
     * @param $rule
     *
     * @return $this
     */
    public function strategy($rule)
    {
        $this->strategies[] = $rule;

        return $this;
    }

    /**
     * @return array
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function take($limit)
    {
        $this->engine()->setLimit((int)$limit);

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function page($offset)
    {
        $this->engine()->setPage((int)$offset);

        return $this;
    }
}