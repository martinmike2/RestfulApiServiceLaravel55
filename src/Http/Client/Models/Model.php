<?php namespace Entrack\RestfulAPIService\Http\Client\Models;

use Entrack\RestfulAPIService\Http\Client\Connections\Connection;
use Entrack\RestfulAPIService\Http\Client\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Entrack\RestfulAPIService\Http\Client\Relations\HasOne;
use Entrack\RestfulAPIService\Http\Client\Relations\HasMany;
use Entrack\RestfulAPIService\Http\Client\Relations\HasManyArray;
use Illuminate\Support\MessageBag;


class Model implements ModelInterface {

    /**
     * The resource associated with the model.
     *
     * @var string
     */
    protected $resource;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Indicates if the model exists.
     *
     * @var bool
     */
    public $exists = false;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The loaded relationships for the model.
     *
     * @var array
     */
    protected $relations = array();

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array();

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->fill($attributes);

        $this->boot();
    }

    /**
     * A boot method to call on initialization
     *
     * @return void
     */
    public function boot() {}

    /**
     * Return all from resource
     *
     * @param array $fields
     * @return mixed
     */
    public static function all($fields = ['*'])
    {
        return static::query()->get($fields);
    }

    /**
     * Find an individual resource by Id
     *
     * @param $id
     * @param array $fields
     * @return mixed
     */
    public static function find($id, $fields = ['*'])
    {
        $instance = new static;

        if (is_array($id) && empty($id)) return $instance->newCollection();

        return $instance->newQuery()->find($id, $fields);
    }

    /**
     * Allows a manual call to the
     *
     * @return \Entrack\RestfulAPIService\Http\Client\Models\ModelClientRepository
     */
    public static function client()
    {
        $instance = with(new static);
        $connection = $instance->getConnection();

        return new ModelClientRepository($connection, $instance);
    }

    /**
     * Create a new resource
     *
     * @param array $attributes
     * @return mixed
     */
    public static function create($attributes = [])
    {
        $instance = new static;
        return static::client()->post($instance->getResource(), ['data' => $attributes]);
    }

    /**
     * Update an existing resource
     *
     * @param array $attributes
     * @return mixed
     */
    public static function update($attributes = [])
    {
        $instance = new static;
        return static::client()->patch($instance->getResource() . '/' .$attributes['id'], ['data' => $attributes]);
    }

    /**
     * Delete an existing resource
     *
     * @param int $id
     * @return mixed
     */
    public static function delete($id)
    {
        $instance = new static;
        return static::client()->delete($instance->getResource() . '/' .$id, ['data' => ['id' => $id]]);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     */
    public function fill(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Begin querying a model with includes.
     *
     * @param  array|string  $relations
     * @return \Entrack\RestfulAPIService\Http\Client\Models\Builder
     */
    public static function with($relations)
    {
        if (is_string($relations)) $relations = func_get_args();

        $instance = new static;

        return $instance->newQuery()->with($relations);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool   $exists
     * @return static
     */
    public function newInstance($attributes = array(), $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array) $attributes);

        $model->exists = $exists;

        return $model;
    }

    /**
     * Begin querying the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Get the resource associated with the model.
     *
     * @return string
     */
    public function getResource()
    {
        if (isset($this->table)) return $this->table;

        if (isset($this->resource)) return $this->resource;

        return str_replace('\\', '', snake_case(str_plural(class_basename($this))));
    }

    /**
     * Set the resource associated with the model.
     *
     * @param  string  $resource
     * @return void
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function getTable()
    {
        return $this->getResource();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Entrack\RestfulAPIService\Http\Client\Models\Builder
     */
    public function newQuery()
    {
        $builder = $this->newModelBuilder(
            $this->newQueryBuilder()
        );

        return $builder->setModel($this)->with($this->with);
    }

    /**
     * Create a new Model query builder for the model.
     *
     * @param  \Entrack\RestfulAPIService\Http\Client\Query\Builder $query
     * @return \Entrack\RestfulAPIService\Http\Client\Models\Builder
     */
    public function newModelBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Entrack\RestfulAPIService\Http\Client\Query\Builder
     */
    protected function newQueryBuilder()
    {
        return new QueryBuilder($this->getConnection());
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newFromBuilder($attributes = array())
    {
        $instance = $this->newInstance(array(), true);

        $instance->setRawAttributes((array) $attributes, true);

        return $instance;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $inAttributes = array_key_exists($key, $this->attributes);

        return $inAttributes ? $this->attributes[$key] : null;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @return void
     */
    public function setRawAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        return $this->getArrayableAttributes();
    }

    /**
     * Get an attribute array of all arrayable attributes.
     *
     * @return array
     */
    protected function getArrayableAttributes()
    {
        return $this->getArrayableItems($this->attributes);
    }

    /**
     * Get the model's relationships in array form.
     *
     * @return array
     */
    public function relationsToArray()
    {
        $attributes = array();

        foreach ($this->getArrayableRelations() as $key => $value)
        {
            if (in_array($key, $this->hidden)) continue;

            // If the values implements the Arrayable interface we can just call this
            // toArray method on the instances which will convert both models and
            // collections to their proper array form and we'll set the values.
            if ($value instanceof ArrayableInterface)
            {
                $relation = $value->toArray();
            }

            // If the value is null, we'll still go ahead and set it in this list of
            // attributes since null is used to represent empty relationships if
            // if it a has one or belongs to type relationships on the models.
            elseif (is_null($value))
            {
                $relation = $value;
            }

            // If the relationships snake-casing is enabled, we will snake case this
            // key so that the relation attribute is snake cased in this returned
            // array to the developers, making this consistent with attributes.
            if (static::$snakeAttributes)
            {
                $key = snake_case($key);
            }

            // If the relation value has been set, we will set it on this attributes
            // list for returning. If it was not arrayable or null, we'll not set
            // the value on the array because it is some type of invalid value.
            if (isset($relation) || is_null($value))
            {
                $attributes[$key] = $relation;
            }

            unset($relation);
        }

        return $attributes;
    }

    /**
     * Get an attribute array of all arrayable relations.
     *
     * @return array
     */
    protected function getArrayableRelations()
    {
        return $this->getArrayableItems($this->relations);
    }

    /**
     * Get an attribute array of all arrayable values.
     *
     * @param  array  $values
     * @return array
     */
    protected function getArrayableItems(array $values)
    {
        return array_diff_key($values, array_flip($this->hidden));
    }

    /**
     * @return \Entrack\RestfulAPIService\Http\Client\Connections\Connection
     */
    protected function getConnection()
    {
        return new Connection($this->connection);
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Get all the loaded relations for the instance.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get a specified relationship.
     *
     * @param  string  $relation
     * @return mixed
     */
    public function getRelation($relation)
    {
        return $this->relations[$relation];
    }

    /**
     * Set the specific relationship in the model.
     *
     * @param  string  $relation
     * @param  mixed   $value
     * @return $this
     */
    public function setRelation($relation, $value)
    {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * Set the entire relations array on the model.
     *
     * @param  array  $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set the hidden attributes for the model.
     *
     * @param  array  $hidden
     * @return void
     */
    public function setHidden(array $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Support\Collection
     */
    public function newCollection(array $models = array())
    {
        return new Collection($models);
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->attributesToArray();

        return array_merge($attributes, $this->relationsToArray());
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
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
        $query = $this->newQuery();

        return call_user_func_array(array($query, $method), $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        if(method_exists($instance, $method))
        {
            return call_user_func_array(array($instance, $method), $parameters);
        }

        return $instance->__call($method, $parameters);
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param object $parent
     * @param string $foreign_key
     * @param string $local_key
     * @return \Entrack\RestfulAPIService\Http\Client\Relations\HasOne
     */
    public static function hasOne($parent, $foreign_key, $local_key)
    {
        return new HasOne(static::query(), $parent, $foreign_key, $local_key);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param object $parent
     * @param string $foreign_key
     * @param string $local_key
     * @return \Entrack\RestfulAPIService\Http\Client\Relations\HasMany
     */
    public static function hasMany($parent, $foreign_key = null, $local_key = null)
    {
        return new HasMany(static::query(), $parent, $foreign_key, $local_key);
    }

    /**
     * @param $parent
     * @param null $foreign_key
     * @param null $local_key
     * @return HasManyArray
     */
    public static function hasManyArray($parent, $foreign_key = null, $local_key = null)
    {
        return new HasManyArray(static::query(), $parent, $foreign_key, $local_key);
    }
}