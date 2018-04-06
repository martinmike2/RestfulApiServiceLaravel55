<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes;

use Entrack\RestfulAPIService\HttpQuery\HttpQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IncludesScope implements Scope
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
    protected $includes;

    protected $query;

    protected $builder;

    protected $model;

    protected $has;
    /**
     * Instantiate the scope
     *
     * @param array $includes
     */
    public function __construct(array $includes, HttpQuery $query)
    {
        $this->includes = $includes ?: [];
        $this->query = $query;
        $this->has = $query->getHas() ?: [];
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
        $this->setEagerLoads();
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder)
    {
        $builder->setEagerLoads(
            array_except(
                $builder->getEagerLoads(),
                $this->includes
            )
        );
    }

    public function setEagerLoads()
    {
        if(empty($this->includes)) return;

        $closures = array_map(
            function($include) {
                return function($builder) use($include) {
                    $scopes = $this->query->getType($include);
                    $model = $builder->getModel();

                    if(in_array($include, $this->has)) {
                        $scopes = array_except($scopes, ['has_filter']);
                    }

                    foreach($scopes as $scope => $value) {
                        $class = 'Entrack\\RestfulAPIService\\HttpQuery\\Eloquent\\Scopes\\' . studly_case($scope . '_Scope');
                        if(class_exists($class)) {
                            $model::addGlobalScope(new $class($value));
                        }
                    }
                };
            },
            $this->includes
        );

        $this->builder->with(array_combine($this->includes, $closures));
    }

    /**
     * Filter includes to only available relationship methods
     *
     * @return mixed
     */
    protected function filterIncludes()
    {
        $this->includes = array_filter(
            $this->includes,
            function($include)
            {
                $model = $this->model;
                return method_exists($model, $this->getIncludeParent($include));
            }
        );
    }

    /**
     * Get the parent of a nested include
     *
     * @param $include
     * @return mixed
     */
    protected function getIncludeParent($include)
    {
        return head(explode('.', $include));
    }
}