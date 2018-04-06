<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Query;

use Entrack\RestfulAPIService\Database\PostgreSQL\Helpers\ArrayHelper as Arr;

trait ArrayColumnQueryTrait
{
    /**
     * Query a postgres array type column
     *
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function whereArray($column, $operator = null, $value = null, $boolean = 'and')
    {
        if(in_array($value, ['and', 'or'])) {
            $boolean = $value;
            $value = null;
        }

        if (!$value) {
            $value = $operator;
            $operator = '&&';
        }

        $value = Arr::arrayToString((array)$value);

        return $this->whereRaw($column . ' ' . $operator . ' ' . "'$value'", [], $boolean);
    }

    /**
     * Find rows containing a value
     * or an array of values
     *
     * @param $column
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function whereInArray($column, $value = null, $boolean = 'and')
    {
        return $this->whereArray($column, $value, $boolean);
    }

    /**
     * Alias for whereInArray
     *
     * @param $column
     * @param null $value
     * @param string $boolean
     * @return JsonQueryBuilder
     */
    public function whereContains($column, $value = null, $boolean = 'and')
    {
        return $this->whereInArray($column, $value, $boolean);
    }

    /**
     * Find rows not containing a value
     * or an array of values
     *
     * @param $column
     * @param null $value
     * @param string $boolean
     * @return JsonQueryBuilder
     */
    public function whereNotInArray($column, $value = null, $boolean = 'and')
    {
        return $this->whereArray('NOT ' . $column, $value, $boolean);
    }

    /**
     * Alias for whereNotInArray
     *
     * @param $column
     * @param $value
     * @param string $boolean
     * @return JsonQueryBuilder
     */
    public function whereDoesNotContain($column, $value, $boolean = 'and')
    {
        return $this->whereNotInArray($column, $value, $boolean);
    }

    /**
     * Find items containing only a value or array
     * of values
     *
     * @param $column
     * @param null $value
     * @param string $boolean
     * @return JsonQueryBuilder
     */
    public function whereArrayOnly($column, $value = null, $boolean = 'and')
    {
        return $this->whereArray($column, '<@', $value, $boolean);
    }

    /**
     * Alias for whereOnlyArray
     *
     * @param $column
     * @param $value
     * @param string $boolean
     * @return JsonQueryBuilder
     */
    public function whereContainsOnly($column, $value, $boolean = 'and')
    {
        return $this->whereArrayOnly($column, $value, $boolean);
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        return parent::whereIn($column, $this->arrayValues($values), $boolean, $not);
    }

    /**
     * Convert postgres array values
     * to an array
     *
     * @param $array
     * @return array
     */
    public function arrayValues($array)
    {
        if($array instanceof \Closure) {
            return $array;
        }

        $values = [];

        foreach((array) $array as $value) {
            if(Arr::isArray($value)) {
                $values = array_merge($values, Arr::stringToArray($value));
                continue;
            }

            $values[] = $value;
        }

        return $values;
    }
}