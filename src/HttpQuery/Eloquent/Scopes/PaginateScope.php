<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaginateScope implements Scope
{

    /**
     * Set whether the instance has
     * already been paginated
     *
     * @var bool
     */
    public static $paginated = false;

    /**
     * Query parser paginate
     *
     * @var array
     */
    protected $paginate;

    /**
     * Query pagination params
     *
     * @param $paginate
     */
    public function __construct($paginate)
    {
        $this->paginate = $paginate;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if(static::$paginated === false && !is_null($this->paginate))
        {
            $builder->forPage($this->paginate['page'],$this->paginate['per_page']);
            $builder->setPaginated(true);
        }

        static::$paginated = true;
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder) {}

    public function getPaginate()
    {
        return $this->paginate;
    }

}