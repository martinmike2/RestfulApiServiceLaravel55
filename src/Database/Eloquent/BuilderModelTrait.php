<?php namespace Entrack\RestfulAPIService\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;

trait BuilderModelTrait {

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

}