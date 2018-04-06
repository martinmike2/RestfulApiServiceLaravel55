<?php namespace Entrack\RestfulAPIService\HttpQuery;

use Illuminate\Support\Collection;

class HttpQuery
{

    /**
     * Query params instance
     * @var /Illuminate\Support\Collection
     */
    protected $query;

    /**
     * The key for default query
     * parameters
     *
     * @var string
     */
    protected $default_key;

    /**
     * The included relationships
     * for the query
     *
     * @var array
     */
    protected $includes;

    /**
     * The included relationships
     * for the query
     *
     * @var array
     */
    protected $has;

    /**
     * Available arg keys
     *
     * @var array
     */
    protected $keys = ['sort', 'fields', 'paginate', 'filter', 'has_filter', 'search'];

    /**
     * Available filter operators
     *
     * @var array
     */
    protected $operators = ['=', '!=', '<>', '>', '<', '>=', '<=', '!<', '!>', 'IN', 'NOT IN', 'NULL', 'NOT NULL'];

    /**
     * Instatiate the HttpQuery Class
     *
     * @param $sort
     * @param $fields
     * @param $paginate
     * @param $filter
     * @param $has_filter
     * @param $search
     */
    public function __construct($sort = null, $fields = null, $paginate = null, $filter = null, $has_filter = null, $search = null)
    {
        $this->setQuery(
            array_combine(
                $this->keys,
                [$sort, $fields, $paginate, $filter, $has_filter, $search]
            )
        );
    }

    public function setQuery(array $query)
    {
        $this->query = new Collection(array_filter($query));
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getIncludes()
    {
        return $this->includes;
    }

    public function setIncludes(array $includes)
    {
        $this->includes = array_filter($includes) ?: [];
    }

    public function getHas()
    {
        return $this->has;
    }

    public function setHas(array $has)
    {
        $this->has = array_filter($has) ?: [];
    }

    /**
     * The key for default query parameters
     *
     * @param $key
     */
    public function setDefaultKey($key)
    {
        $this->default_key = $key;
    }

    /**
     * Return the list of IDs
     *
     * @return mixed
     */
    public function id()
    {
        return $this->query->get('id');
    }

    /**
     * Return the sorting parameters
     *
     * @return mixed
     */
    public function sort()
    {
        return array_map(
            function($sort) {

                return (string) $sort[0] === '-'
                    ? [substr($sort, 1), 'DESC']
                    : [$sort, 'ASC'];

            },
            $this->query->get('sort')
        );
    }

    /**
     * Return the requested relationships
     *
     * @return mixed
     */
    public function includes()
    {
        return array_map(
            function($v) {

                return str_replace(['-','/'],'_',$v);

            },
            $this->query->get('includes', [])
        );
    }

    /**
     * Return a list of the requested entity properties
     *
     * @return mixed
     */
    public function fields()
    {
        return $this->query->get('fields', ['*']);
    }

    /**
     * Return the requested pagination
     *
     * @return mixed
     */
    public function paginate()
    {
        $param = $this->query->get('paginate', []);

        switch (count($param)) {
            case 0:
                return null;
                break;
            case 1:
                $page = 1;
                $per_page = $param[0];
                break;
            case 2:
                $page = $param[0];
                $per_page = $param[1];
                break;
        }

        return [
            'page' => (int) $page,
            'per_page' => (int) $per_page
        ];
    }

    /**
     * Return the filters array
     *
     * @return array
     */
    public function filter()
    {
        return Composers\FilterQueryComposer::compose((array) $this->query->get('filter', []));
    }

    /**
     * Return the filters array
     *
     * @return array
     */
    public function has_filter()
    {
        return Composers\FilterQueryComposer::compose((array) $this->query->get('has_filter', []));
    }

    public function search()
    {
        return $this->query->get('search', []);
    }

    /**
     * Get the default query params
     *
     * @return mixed
     */
    public function getDefaults()
    {
        $defaults = $this->transform(
            function($value) {
                if($this->isNestedQueryArray($value)) {
                    return null;
                }
                return $value;
            }
        )->toArray();

        return array_merge(
            $this->getType($this->default_key),
            $defaults
        );
    }

    /**
     * Get values by type
     *
     * @param $type
     * @param bool $defaults
     * @return mixed
     */
    public function getType($type)
    {
        return $this->transform(
            function($value) use($type) {
                if($this->isNestedQueryArray($value)) {
                     return $this->query->make($value)->get($type);
                }
            }
        )->toArray();
    }

    public function isNestedQueryArray(array $array)
    {
        return !is_null($array) && count(array_filter($array, 'is_array')) && $this->isAssoc($array);
    }

    public function isAssoc($array)
    {
        $array = array_keys($array);
        return ($array !== array_keys($array));
    }

    /**
     * Transform the query
     *
     * @param \Closure $closure
     * @return mixed
     */
    protected function transform(\Closure $closure)
    {
        $keys = $this->query->keys()->toArray();
        $values = $this->query->map($closure)->toArray();

        $args = array_merge(
            $this->getArgsAsNullArray(),
            array_combine($keys, $values)
        );

        return $this->newInstance($args);

    }

    /**
     * Create a new HttpQuery instance
     *
     * @param array $args
     * @return object
     */
    protected function newInstance(array $args)
    {
        $class = new \ReflectionClass(HttpQuery::class);
        $default = $this->getArgsAsNullArray();

        return $class->newInstanceArgs(
            array_merge($default, $args)
        );
    }

    /**
     * Get an array of keys with null values
     *
     * @return array
     */
    protected function getArgsAsNullArray()
    {
        $keys = $this->keys;
        $values = array_map( function() { return null; }, $keys );

        return array_combine($keys, $values);
    }

    /**
     * Allow for property-style retrieval
     *
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->query->get($property);
    }

    /**
     * Set a piece of data on the record.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        if($this->query->has($key)) {
            $this->query->put($key, $value);
        }
    }

    /**
     * Check if a piece of data is bound to the record.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->query->has($key);
    }

    /**
     * Remove a piece of bound data from the record.
     *
     * @param  string  $key
     * @return bool
     */
    public function __unset($key)
    {
        $this->query->forget($key);
    }

    /**
     * Get the instance as an array
     * with extended attributes
     *
     * @return array
     */
    public function toArray()
    {
        $keys = $this->query->keys()->toArray();
        $values = $this->query->map(
            function($value, $key) {
                return $this->{$key}();
            }
        )->toArray();

        return array_combine($keys, $values);
    }

    /**
     * Call methods from the Collection dependency
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->query, $method], $arguments);
    }
}
