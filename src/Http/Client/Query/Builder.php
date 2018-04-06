<?php namespace Entrack\RestfulAPIService\Http\Client\Query;

use Closure;
use Entrack\RestfulAPIService\Http\Client\Connections\ConnectionInterface;

class Builder
{

    /**
     * The resource which the query is targeting.
     *
     * @var string
     */
    public $from;

    /**
     * The resource path the query is targeting.
     *
     * @var string
     */
    public $path;


    /**
     * The query array
     *
     * @var array
     */
    protected $query = [
        'include' => [],
        'has' => [],
        'fields' => [],
        'paginate' => [],
        'filter' => [],
        'has_filter' => [],
        'sort' => []
    ];

    /**
     * Create a new query builder instance.
     *
     * @param  \Entrack\RestfulAPIService\Http\Client\Connections\ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set the resource which the query is targeting.
     *
     * @param  string $resource
     * @return $this
     */
    public function from($resource)
    {
        $this->from = $resource;

        return $this;
    }

    public function resourceKey()
    {
        return head(explode('/', $this->from));
    }

    /**
     * Set a path for resource url
     *
     * @param  string $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get th path for the resource url
     *
     * @return string
     */
    public function getPath()
    {
        return is_null($this->path) ? "" : '/'.$this->path;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param int $id
     * @param array $fields
     * @return mixed|static
     */
    public function find($id, $fields = ['*'])
    {
        return $this->where('id', '=', $id)->first($fields);
    }

    /**
     * Pluck a single column's value from the first result of a query.
     *
     * @param string $field
     * @return mixed
     */
    public function pluck($field)
    {
        $result = (array) $this->first([$field]);

        if(count($result['data']))
        {
            $result['data'] = reset($result['data']);
        }
        else
        {
            $result['data'] = null;
        }

        return $result;
    }

    /**
     * Execute the query and get the first result.
     *
     * @param array $fields
     * @return mixed|static
     */
    public function first($fields = ['*'])
    {
        $results = $this->get($fields);

        if(count($results['data']))
        {
            $results['data'] = reset($results['data']);
        }
        else
        {
            $results['data'] = null;
        }

        return $results;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $fields
     * @return array
     */
    public function get($fields = ['*'])
    {
        $this->fields($fields);

        return $this->connection->get($this->from . $this->getPath(), $this->getQuery());
    }

    /**
     * Set the query includes
     *
     * @param array $includes
     */
    public function includes(array $includes)
    {
        $this->query['include'] = $this->format(array_keys($includes));

        foreach($includes as $query) {
            $this->query = array_merge_recursive(
                $this->query,
                $query
            );
        }
    }

    /**
     * Set the query includes
     *
     * @param array $has
     */
    public function has(array $has)
    {
        $this->query['has'] = $this->format(array_keys($has));

        foreach($has as $query) {

            $filters = array_pull($query, 'filter', []);
            $query = array_set(
                $query,
                'has_filter',
                $filters
            );

            $this->query = array_merge_recursive(
                $this->query,
                $query
            );
        }
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $direction = strtolower($direction) == 'desc' ? '-' : '';
        $this->query['sort'][$this->resourceKey()] = $direction.$column;

        return $this;
    }

    /**
     * Add a "search" to the query.
     *
     * @param  array/string  $query
     * @return $this
     */
    public function search($query)
    {
        if(!is_array($query)) {
            $query = (array) $query;
        }

        $query = array_map(
            function($s) {
                $s = trim($s);
                $s = preg_replace('/[^a-z0-9\s]/i', ' ', $s);
                return preg_replace('!\s+!', ' ', $s);
            },
            $query
        );

        $this->query['search'][$this->resourceKey()] = $this->format($query);

        return $this;
    }

    /**
     * Set the query pagination
     *
     * @param int $per_page
     * @param int $page
     * @return $this
     */
    public function paginate($per_page = 15, $page = 1)
    {
        $this->query['paginate'][$this->resourceKey()] = $this->format([$page, $per_page]);

        return $this;
    }

    /**
     * Set the query fields
     *
     * @param array $fields
     */
    public function fields(array $fields)
    {
        if (reset($fields) === '*') {
            return;
        }

        $this->query['fields'][$this->resourceKey()] = $this->format($fields);
    }

    /**
     * Set a query filter if value is not empty
     *
     * @param $attribute
     * @param null $operator
     * @param null $value
     * @return boolean
     */
    public function isValidFilter($attribute, $operator = null, $value = null)
    {
        return !is_null($operator);
    }

    /**
     * Add a query filter
     *
     * @param $attribute
     * @param null $operator
     * @param null $value
     */
    public function addFilter($attribute, $operator = null, $value = null, $boolean = 'and')
    {
        if(!$this->isValidFilter($attribute, $operator, $value)) return;

        $filters = $this->getFilters();

        $filter = array_filter(
            compact('attribute', 'operator', 'value'),
            function($v) {
                return is_null($v) ? false : true;
            }
        );

        $filter = implode(',', array_flatten($filter));

        if(!array_key_exists($boolean, $filters)) {
            $filters[$boolean] = [];
        }

        $filters[$boolean][] = $filter;

        $this->setFilters($filters);
    }

    /**
     * Add a raw query filter
     *
     * @param mixed $filter
     * @param string $boolean
     */
    public function addRawFilter($filter, $boolean)
    {
        $filters = $this->getFilters();

        if(!array_key_exists($boolean, $filters)) {
            $filters[$boolean] = [];
        }

        $filters[$boolean][] = $filter;

        $this->setFilters($filters);
    }

    /**
     * Get the Filters for the Query
     *
     * @return array
     */
    public function getFilters()
    {
        $resource = $this->resourceKey();
        return isset($this->query['filter'][$resource]) ? $this->query['filter'][$resource] : [];
    }

    /**
     * Set the Query Filters
     *
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->query['filter'][$this->resourceKey()] = $filters;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string  $attribute
     * @param  string  $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($attribute, $operator = null, $value = null, $boolean = 'and')
    {
        if($attribute instanceof Closure) {
            return $this->whereNested($attribute, $boolean);
        }

        if(is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        $this->addFilter($attribute, $operator, $value, $boolean);

        return $this;
    }

    /**
     * Add a basic or where clause to the query.
     *
     * @param  string  $attribute
     * @param  string  $operator
     * @param  mixed   $value
     * @return $this
     */
    public function orWhere($attribute, $operator = null, $value = null)
    {
        return $this->where($attribute, $operator, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereIn($attribute, $values, $boolean = 'and')
    {
        return $this->where($attribute, 'IN', $values, $boolean);
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @return $this
     */
    public function orWhereIn($attribute, $values)
    {
        return $this->whereIn($attribute, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotIn($attribute, $values, $boolean = 'and')
    {
        if(!empty(array_filter($values))) {
            return $this->where($attribute, 'NOT IN', $values, $boolean);
        }

        return $this;
    }

    /**
     * Add a "or where not in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @return $this
     */
    public function orWhereNotIn($attribute, $values)
    {
        return $this->whereNotIn($attribute, $values, 'or');
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string  $attribute
     * @param  string  $boolean
     * @return $this
     */
    public function whereNull($attribute, $boolean = 'and')
    {
        return $this->where($attribute, 'NULL', null, $boolean);
    }

    /**
     * Add a "or where null" clause to the query.
     *
     * @param  string  $attribute
     * @return $this
     */
    public function orWhereNull($attribute)
    {
        return $this->whereNull($attribute, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  string  $attribute
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotNull($attribute, $boolean = 'and')
    {
        return $this->where($attribute, 'NOT NULL', null, $boolean);
    }

    /**
     * Add a "or where not null" clause to the query.
     *
     * @param  string  $attribute
     * @return $this
     */
    public function orWhereNotNull($attribute)
    {
        return $this->whereNotNull($attribute, 'or');
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param  Closure $callback
     * @param  string   $boolean
     * @return \Entrack\RestfulAPIService\Http\Client\Query\Builder|static
     */
    public function whereNested(Closure $callback, $boolean = 'and')
    {
        $query = $this->newQuery();

        $query->from($this->from);

        call_user_func($callback, $query);

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param  \Entrack\RestfulAPIService\Http\Client\Query\Builder|static $query
     * @param  string  $boolean
     * @return $this
     */
    public function addNestedWhereQuery($query, $boolean = 'and')
    {
        $filters = $this->getFilters();

        $nested = [
            'nested' => [
                $boolean => [$query->getFilters()]
            ]
        ];

        $this->setFilters(
            array_merge_recursive($filters, $nested)
        );

        return $this;
    }

    /**
     * Format a query parameter array to string
     *
     * @param $items
     * @param string $sep
     * @return mixed
     */
    public function format($items, $sep = ',')
    {
        return implode($sep, $items);
    }

    /**
     * Create a new query
     *
     * @return $this
     */
    public function newQuery()
    {
        return new static($this->connection);
    }

    /**
     * Get the query
     *
     * @return array
     */
    public function getQuery()
    {
        return array_filter($this->query);
    }

    /**
     * Set the query
     *
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

}