<?php namespace Entrack\RestfulAPIService\HttpQuery\Parsers;

use Illuminate\Support\Collection;

class FilterQueryParser {

    /**
     * The parsed query
     *
     * @var array
     */
    protected $parsed;

    /**
     * The accepted query types
     *
     * @var array
     */
    protected $query_types = ['and', 'or', 'nested'];

    /**
     * FilterQueryParser constructor.
     */
    public function __construct()
    {
        $this->parsed = $this->blankQueryTypeArray();
    }

    /**
     * Parse a query array to return the
     * correct format for the filter queries
     *
     * @param array $filters
     * @return array
     */
    public static function parse(array $filters)
    {
        $instance = new static;
        $queries = [];

        foreach($filters as $type => $filter) {
            $query = $type === 'nested'
                ? $instance->parseNestedQuery($filter, $type)
                : $instance->parseQuery($filter, $type);
            $queries = array_merge($queries, $query);
        }

        return $queries;

    }

    /**
     * Parse a single query item
     *
     * @param $query
     * @param string $type
     * @return array
     */
    protected function parseQuery($query, $type)
    {
        // The first item in the array
        $first = is_array($query) ? head($query) : $query;
        $type = $this->isQueryTypeAvailable($type) ? $type : 'and';

        // If its already a query type array
        // great! Let's just return that
        if(!is_string($first) && $this->isQueryTypeArray($query)) {
            $this->setParsed($query);
        }

        // Check if the array is the actual query
        // and not an array of arrays
        else if($first && is_string($first)) {
            $this->addQueryByType($type, $query);
        }

        // Check if the array of arrays
        // is not a type query and add it
        else if($first && !$this->isQueryTypeArray($first)) {
            foreach($query as $key => $value) {
                $this->addQueryByType($type, $value);
            }
        }

        // We've got an array of type queries here, so let's just
        // parse and recursively merge the queries
        else {
            foreach($query as $key => $value) {
                $parsed = static::parse($query);
                $this->mergeParsed($parsed);
            }
        }

        return $this->getFilteredQuery();
    }

    protected function parseNestedQuery($query, $type)
    {
        $instance = new static;
        foreach($query as $key => $val) {
            $qtype = $instance->isQueryTypeAvailable($key, $type) ? $key : 'and';
            $instance->addQueryByType($qtype, head($val));
        }

        return [ $type => $instance->getFilteredQuery() ];
    }

    /**
     * Set the parsed query property
     *
     * @param array $query
     * @return void
     */
    public function setParsed(array $query)
    {
        foreach($query as $key => $value) {
            if($this->isQueryTypeAvailable($key) && !empty($value)) {
                $this->addQueryByType($key, head($value));
            }
        }
    }

    /**
     * Get the parsed query property
     *
     * @return array
     */
    public function getParsed()
    {
        return $this->parsed;
    }

    /**
     * Filter the parsed query and
     * remove any empty queries
     *
     * @return array
     */
    public function getFilteredQuery()
    {
        return $this->arrayFilterRecursive($this->parsed);
    }

    /**
     * Merge an array of item into
     * the current parsed array
     *
     * @param array $items
     */
    protected function mergeParsed(array $items)
    {
        $merged = array_merge_recursive($this->parsed, $items);
        $this->setParsed($merged);
    }

    /**
     * Add a query to the parsed
     * property type array
     *
     * @param $type
     * @param array $query
     */
    public function addQueryByType($type, array $query)
    {
        $this->parsed[$type][] = $query;
    }

    /**
     * Get the queries from
     *
     * @param $type
     * @return array
     */
    public function getQueriesByType($type)
    {
        if($this->isQueryTypeAvailable($type)) {
            return $this->parsed[$type];
        }

        return [];
    }

    /**
     * Checks to see if current query is an
     * array of query_types
     *
     * @param array $query
     * @param string|array except
     * @return bool
     */
    public function isQueryTypeArray(array $query, $except = [])
    {
        $keys = array_map('strtolower', array_keys($query));
        $types = $this->getTypes($except);

        if(array_intersect($types, $keys)) {
            return true;
        }

        return false;
    }

    /**
     * Create a collection array of
     * Query Types
     *
     * @return array
     */
    protected function blankQueryTypeArray($except = [])
    {
        $types = $this->getTypes($except);
        $array = array_map(
            function() { return []; },
            array_flip($types)
        );

        return array_combine($types, $array);
    }

    /**
     * Check if the query type is accepted
     *
     * @param $type
     * @return bool
     */
    protected function isQueryTypeAvailable($type, $except = [])
    {
        return in_array($type, $this->getTypes($except), true);
    }

    /**
     * Filter an array recursively for empty values
     *
     * @param array $array
     * @return array
     */
    protected function arrayFilterRecursive(array $array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->arrayFilterRecursive($value);
            }
        }

        return array_filter($array);
    }


    public function getTypes($except = [])
    {
        return empty($except)
            ? $this->query_types
            : array_flip(array_except(array_flip($this->query_types), (array) $except));
    }
}