<?php namespace Entrack\RestfulAPIService\Http\Client\Models;

use Entrack\RestfulAPIService\Http\Client\Query\Builder as QueryBuilder;
use Entrack\RestfulAPIService\Resources\ResourceClientModel;

class Builder {

    /**
     * The model instance
     *
     * @var \Entrack\RestfulAPIService\Http\Client\Models\Model
     */
    protected $model;

    /**
     * The relationships that should be included.
     *
     * @var array
     */
    protected $includes = [];

    /**
     * The relationships to check for existence
     *
     * @var array
     */
    protected $has = [];

    /**
     * Query builder instance
     *
     * @var \Entrack\RestfulAPIService\Http\Client\Query\Builder
     */
    protected $query;

    /**
     * Create a new Model query builder instance.
     *
     * @param  \Entrack\RestfulAPIService\Http\Client\Query\Builder  $query
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Get the model instance being queried.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param  \Entrack\RestfulAPIService\Http\Client\Models\Model  $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->query->from($model->getResource());

        return $this;
    }

    public function getQuery()
    {
        return $this->query->getQuery();
    }

    /**
     * Set the relationships that should be included.
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function with($relations)
    {
        if (is_string($relations)) $relations = func_get_args();

        $includes = $this->parseRelations($relations);

        $this->includes = array_merge($this->includes, $includes);

        $this->query->includes($includes);

        return $this;
    }

    /**
     * Set the relationships for a has match.
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function has($relations)
    {
        if (is_string($relations)) $relations = func_get_args();

        $has = $this->parseRelations($relations);

        $this->has = array_merge($this->has, $has);

        $this->query->has($has);

        return $this;
    }

    /**
     * Parse a list of relations into individuals.
     *
     * @param  array  $relations
     * @return array
     */
    protected function parseRelations(array $relations)
    {
        $results = array();

        foreach ($relations as $name => $constraints)
        {
            // If the "relation" value is actually a numeric key, we can assume that no
            // constraints have been specified for the eager load and we'll just put
            // an empty Closure with the loader so that we can treat all the same.
            if (is_numeric($name))
            {
                list($name, $constraints) = array($constraints, null);
            }

            $relation = with(new Model())->query();
            $relation->from($name);

            if(!is_null($constraints)) {
                call_user_func($constraints, $relation);
            }

            $results[$name] = $relation->getQuery();
        }

        return $results;
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function find($id, $columns = array('*'))
    {
        $this->query->where('id', '=', $id);

        return $this->first($columns);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function first($fields = ['*'])
    {
        return $this->get($fields)->first();
    }

    /**
     * Execute the query.
     *
     * @param  array  $fields
     * @return \Illuminate\Support\Collection
     */
    public function get($fields = ['*'])
    {
        $models = $this->getModels($fields);

        return $this->model->newCollection($models);
    }

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array  $fields
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function getModels($fields = ['*'])
    {
        $results = $this->query->get($fields);

        $connection = $this->model->getConnectionName();

        $models = array();

        // Once we have the results, we can spin through them and instantiate a fresh
        // model instance for each records we retrieved from the database. We will
        // also set the proper connection name for the model after we create it.
        foreach ($results['data'] as $result)
        {
            $models[] = $model = $this->model->newFromBuilder($result);

            $model->setConnection($connection);

            if($included = array_get($results, 'included'))
            {
                $this->setRelations($model, $included);
            }

        }

        return $models;
    }

    public function setRelations($model, array $included)
    {
        $links = array_except($model->getAttribute('links'), 'self');

        $relations = [];

        foreach($links as $link => $data)
        {
            $ids = array_fetch(array_get($data,'linkage', []), 'id');
            $attributes = $this->fetchRelation($included, $ids);

            $relation = array_map(
                function($items)
                {
                    return new ResourceClientModel(array_except($items, ['links']));
                },
                $attributes
            );

            $relations = array_merge($relations, [$link => $this->model->newCollection($relation)]);
        }

        $model->setRelations($relations);
    }

    public function fetchRelation(array $included, array $ids)
    {
        return array_where(
            $included,
            function($key, $value) use($ids)
            {
                return in_array($value['id'], $ids);
            }
        );
    }

    public function getFields()
    {
        return [];
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        call_user_func_array(array($this->query, $method), $parameters);

        return $this;
    }
}