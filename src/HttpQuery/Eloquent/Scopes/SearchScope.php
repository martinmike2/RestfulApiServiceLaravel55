<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SearchScope implements Scope
{

    /**
     * Query parser paginate
     *
     * @var array
     */
    protected $search;

    /**
     * Query pagination params
     *
     * @param array $search
     */
    public function __construct(array $search)
    {
        $this->search = $search;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $model = $builder->getModel();
        if(method_exists($builder, 'search')) {
            $builder->search($this->search);
        }
        else if(method_exists($model, 'search')){
            $model->search($this->search);
        }
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder) {}

}