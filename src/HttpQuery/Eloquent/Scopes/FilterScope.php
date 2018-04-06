<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class FilterScope implements Scope
{
    /**
     * Query parser includes
     *
     * @var array
     */
    protected $filters;


    public function __construct(array $filters)
    {
        $this->filters = $filters ?: [];
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        foreach($this->filters as $filter)
        {
            if(array_get($filter, 'type') === 'nested') {
                $this->applyNestedFilterToModel($builder, $filter);
            } else {
                $this->applyFilterToModel($builder, $filter);
            }
        }
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder) {}

    /**
     * Filter includes to only available relationship methods
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return mixed
     */
    protected function applyFilterToModel(Builder $builder, $filter)
    {
        list($attribute, $operator, $value, $type) = array_values($filter);

        if(count(explode('.', $attribute)) == 1) {
            $attribute = $builder->getModel()->getTable() . '.' . $attribute;
        }

        // If the filter composer returns and Expression, then we probably
        // Have a nested query referencing the parent table
        // We need to avoid laravel/postgreSQL turning it into an integer
        // And just run a raw query
        $first = head($value);
        if($first instanceof Expression and count($first) === 1) {
            $builder->whereRaw("$attribute $operator " . $first->getValue(), [] , $type);
            return;
        }

        switch($operator) {
            case 'IN':
                $builder->whereIn($attribute, $value, $type);
                break;
            case 'NOT IN':
                $builder->whereNotIn($attribute, $value, $type);
                break;
            case 'NULL':
                $builder->whereNull($attribute, $type);
                break;
            case 'NOT NULL':
                $builder->whereNotNull($attribute, $type);
                break;
            default:
                $builder->where($attribute, $operator, $value, $type);
                break;
        }
    }

    protected function applyNestedFilterToModel(Builder $builder, $filter)
    {
        list($boolean, $filters) = array_values(array_only($filter, ['attribute', 'value']));

        return $builder->where(function($query) use ($filters) {
            $scope = new static($filters);
            $scope->apply($query);
        }, null, null, $boolean);
    }
}