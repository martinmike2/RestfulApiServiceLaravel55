<?php namespace Entrack\RestfulAPIService\HttpQuery\Composers;

use Entrack\RestfulAPIService\HttpQuery\Parsers\FilterQueryParser as Parser;

class FilterQueryComposer {

    /**
     * Available filter operators
     *
     * @var array
     */
    protected $operators = ['=', '!=', '<>', '>', '<', '>=', '<=', '!<', '!>', 'IN', 'NOT IN', 'NULL', 'NOT NULL'];

    /**
     * The accepted query types
     *
     * @var array
     */
    protected $types = ['and', 'or', 'nested'];

    /**
     * Compose the Query array to pass
     * to the filter scope
     *
     * @param array $filters
     * @return array
     */
    public static function compose(array $filters)
    {
        $instance = new static;
        $filters = Parser::parse($filters);
        $queries = $instance->getQueries($filters);

        return $queries;
    }

    /**
     * Get the formatted array of queries
     * from a parsed filter request query
     *
     * @param array $filters
     * @return array
     */
    protected function getQueries(array $filters)
    {
        $queries = [];

        // Loop through the filter type array and
        // return a proper formatted query array
        foreach($filters as $type => $filter) {
            $map = $type === 'nested'
                ? $this->mapNestedFilter($type, $filter)
                : $this->mapFilter($type, $filter);

            $queries = array_merge($queries, $map);
        }

        // Clean the query array by filtering null values
        // and ignoring duplicated queries
        return array_unique(
            array_filter($queries),
            SORT_REGULAR
        );
    }

    protected function mapFilter($type, $filter)
    {
        return array_map(
            function($query) use ($type) {
                return $this->format($type, $query);
            },
            $filter
        );
    }

    protected function mapNestedFilter($type, $filter)
    {
        $type_queries = [];

        foreach($filter as $t => $f) {
            $type_queries = array_merge_recursive($type_queries, $this->formatNested($t, $f));
        }

        return $type_queries;
    }

    /**
     * Format a parsed query
     *
     * @param $type
     * @param $query
     * @return array|null
     */
    protected function format($type, $query)
    {
        if(empty($query)) return null;

        $attribute = $this->getAttribute($query);
        $operator = $this->getOperator($query);
        $value = $this->formatValue($query);

        // Return the formatted query
        return compact('attribute','operator','value','type');
    }

    protected  function formatNested($type, $query)
    {
        return array_map(
            function($q) use($type) {
                return [
                    'attribute' => $type,
                    'operator' => null,
                    'value' => $this->getQueries($q),
                    'type' => 'nested'
                ];
            },
            $query
        );
    }

    protected function formatValue($value)
    {
        if(is_array($value)) {
            return array_map(
                function($v) {
                    return $this->formatValue($v);
                },
                $value
            );
        }

        $pattern = '/^__(.*?)__$/';
        if (preg_match($pattern, $value)) {
            return preg_replace($pattern, "$1", $value);
        }

        $explode = explode('.', $value);

        if(count($explode) > 1) {
            $explode = array_map(
                function($v) {
                    return "\"$v\"";
                },
                $explode
            );
            $value = \DB::raw(implode('.',$explode));
        }

        return $value;
    }

    /**
     * Get the attribute from a parsed query array
     *
     * @param array $query
     * @return array|mixed
     */
    protected function getAttribute(array &$query)
    {
        // Get the attribute to query
        $attribute = array_shift($query);

        // Check to see if we received an array
        // of arrays and work from there
        if(is_array($attribute)) {
            $tmp = array_shift($attribute);
            $query = $attribute;
            $attribute = $tmp;
        }

        return $attribute;
    }

    /**
     * Get the operator from the parsed query array
     *
     * @param array $query
     * @return array|mixed
     */
    protected function getOperator(array &$query)
    {
        if(in_array(head($query), $this->operators)) {
            return array_shift($query);
        } else if(count($query) == 2) {
            return '=';
        } else {
            return 'IN';
        }
    }
}