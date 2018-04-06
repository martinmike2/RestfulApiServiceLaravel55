<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SortScope implements Scope
{

    /**
     * Query parser paginate
     *
     * @var array
     */
    protected $sort;

    /**
     * Query pagination params
     *
     * @param array $sort
     */
    public function __construct(array $sort)
    {
        $this->sort = $sort;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        foreach($this->sort as $sort) {
            $builder->orderBy($sort[0], $sort[1]);
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