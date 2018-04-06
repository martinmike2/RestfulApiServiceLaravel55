<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent;

use \Entrack\RestfulAPIService\HttpQuery\HttpQuery;
use Illuminate\Database\Eloquent\Builder;

trait HttpQueryTrait
{

    protected static $http_query;

    protected static $parent = true;

    /**
     * Boot the Http Query trait for a model.
     *
     * @return void
     */
    public static function bootHttpQueryTrait()
    {
        if (static::$parent === true) {
            static::setHttpQuery();
            static::setParentScopes();
            static::setIncludeScopes();
            static::setHasScopes();

            static::$parent = false;
        }
    }

    /**
     * Get the Http Query
     *
     * @param string $key
     * @return mixed
     */
    public static function getHttpQuery($key = null)
    {
        return !is_null($key) ? static::$http_query->{$key} : static::$http_query;
    }

    /**
     * Set the Http Query
     *
     * @return void
     */
    public static function setHttpQuery()
    {
        static::$http_query = \App::make(HttpQuery::class);
    }

    /**
     * Set the global query scopes
     *
     * @return void
     */
    protected static function setParentScopes()
    {
        $scopes = static::$http_query->getDefaults();
        static::setQueryScopes($scopes);
    }

    protected static function setIncludeScopes()
    {
        $includes = static::getHttpQuery()->getIncludes() ?: [];
        static::addGlobalScope(new Scopes\IncludesScope($includes, static::$http_query));
    }

    protected static function setHasScopes()
    {
        $has = static::getHttpQuery()->getHas() ?: [];
        static::addGlobalScope(new Scopes\HasScope($has, static::$http_query));
    }

    protected static function setQueryScopes(array $scopes)
    {
        foreach($scopes as $scope => $value) {
            static::setQueryScope($scope, $value);
        }
    }

    protected static function setQueryScope($scope, $value)
    {
        $class = 'Entrack\\RestfulAPIService\\HttpQuery\\Eloquent\\Scopes\\' . studly_case($scope . '_Scope');
        static::addGlobalScope(new $class($value));
    }

    /**
     * Set the global query scopes
     *
     */
    public function scopeIncludes(Builder $builder, $value)
    {
        with(new Scopes\IncludesScope($value, static::$http_query))->apply($builder);
        return $builder;
    }

    public function scopeFields(Builder $builder, $value)
    {
        with(new Scopes\FieldsScope($value))->apply($builder);
        return $builder;
    }

    public function scopeFilter(Builder $builder, $value)
    {
        with(new Scopes\FilterScope($value))->apply($builder);
        return $builder;
    }

    public function scopePaginate()
    {
        with(new Scopes\PaginateScope($value))->apply($builder);
        return $builder;
    }
}