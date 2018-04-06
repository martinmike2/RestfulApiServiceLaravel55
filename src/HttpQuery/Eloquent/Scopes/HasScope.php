<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes;

use Entrack\RestfulAPIService\HttpQuery\HttpQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HasScope extends FilterScope
{
    /**
     * Set whether the Model already has
     * eager loads
     *
     * @var bool
     */
    public static $included = false;

    /**
     * Query parser includes
     *
     * @var array
     */
    protected $has;

    protected $query;

    protected $builder;

    protected $model;

    /**
     * Instantiate the scope
     *
     * @param array $has
     * @param HttpQuery $query
     */
    public function __construct(array $has, HttpQuery $query)
    {
        $this->has = $has ?: [];
        $this->query = $query;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     */
    public function apply(Builder $builder, Model $model)
    {
        $this->builder = $builder;
        $this->model = $model;
        $this->setWhereHas();
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder) {}

    public function setWhereHas()
    {
        if(empty($this->has)) return;

        $closures = array_map(
            function($has) {
                return function($builder) use($has) {
                    $filters = array_get($this->query->getType($has), 'has_filter', []);
                    $scope = new FilterScope($filters);
                    $scope->apply($builder);
                };
            },
            $this->has
        );

        foreach(array_combine($this->has, $closures) as $has => $closure) {
            $this->builder->whereHas($has, $closure);
        }
    }

}