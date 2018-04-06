<?php namespace Entrack\RestfulAPIService\Http\Client\Relations;

use Entrack\RestfulAPIService\Http\Client\Models\Builder;
use Entrack\RestfulAPIService\Http\Client\Models\Model;
use Illuminate\Support\Collection;

abstract class Relation {

    /**
     * The Eloquent query builder instance.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * The parent model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parent;

    /**
     * The related model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $related;

    /**
     * Sets the request as valid or invalid
     *
     * @var boolean
     */
    protected $valid_request = true;

    /**
     * Create a new has many relationship instance.
     *
     * @param  \Entrack\RestfulAPIService\Http\Client\Models\Builder  $query
     * @param  \Entrack\RestfulAPIService\Http\Client\Models\Model  $parent
     */
    public function __construct(Builder $query, $parent)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints() {}

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    abstract public function initRelation(array $models, $relation);

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Support\Collection  $results
     * @param  string  $relation
     * @return array
     */
    abstract public function match(array $models, Collection $results, $relation);

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    abstract public function getResults();

    /**
     * Get the relationship for eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEager()
    {
        if($this->valid_request)
        {
            return $this->get();
        }

       return $this->query->getModel()->newCollection();
    }

    /**
     * Get all of the primary keys for an array of models.
     *
     * @param  array   $models
     * @param  string  $key
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        return array_unique(
            array_values(
                array_map(
                    function($value) use ($key)
                    {
                        return $key ? $value->getAttribute($key) : null;

                    },
                    $models
                )
            )
        );
    }

    /**
     * Get the underlying query for the relation.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array(array($this->query, $method), $parameters);

        if ($result === $this->query) return $this;

        return $result;
    }
}