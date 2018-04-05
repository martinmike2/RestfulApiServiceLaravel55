<?php namespace Entrack\RestfulAPIService\Entities;

use Entrack\RestfulAPIService\Mappers\EloquentEntityRelationshipMapper as Mapper;

trait EntityTrait {

    /**
     * Eloquent model instance
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Model Relationships
     *
     * @var array
     */
    protected $relationships;

    public static $snakeAttributes;

    /**
     * Entity constructor
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->relationships = with(new Mapper($model))->all();
        static::$snakeAttributes = $model::$snakeAttributes;
    }

    /**
     * Return all of the models attributes
     *
     * @return mixed
     */
    public function attributes()
    {
        return $this->modelArrayToEntityArray(
            $this->model->attributesToArray()
        );
    }

    /**
     * Get the type property
     *
     * @return mixed
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Return a JsonApi formatted includes array
     *
     * @return mixed
     */
    public function relationships()
    {
        return $this->relationships;
    }

    /**
     * Returns the entity as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->modelArrayToEntityArray(
            $this->model->toArray()
        );
    }

    /**
     * Pass a models array attributes to update with
     * entity array attributes
     *
     * @param array $attributes
     * @return array
     */
    public function modelArrayToEntityArray(array $attributes)
    {
        $keys = array_keys($attributes);

        $values = array_map(
            function($value, $attribute)
            {
                return $this->getAttribute($attribute, $value);
            },
            $attributes,
            $keys
        );

        return array_combine($keys, $values);
    }

    /**
     * Convert the entity instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the entity attribute or default
     * to model/given attribute
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute($key, $default = null)
    {
        if(method_exists($this, $key))
        {
            return $this->{$key}();
        }

        if(method_exists($this, camel_case($key)))
        {
            return $this->{camel_case($key)}();
        }

        return is_null($default) ? $this->model->{$key} : $default;
    }

    /**
     * Get the model instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get a models attribute
     *
     * @param null $key
     * @return mixed
     */
    public function getModelAttribute($key = null)
    {
        $key = is_null($key) ? debug_backtrace()[1]['function'] : $key;
        return $this->getModel()->getAttribute($key);
    }

    /**
     * Returns the results of a method or model property
     * if it exists
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->getAttribute($property);
    }

    /**
     * Allows isset checks for model attributes
     *
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return $this->getAttribute($name) ? true : false;
    }

    /**
     * Convert the instance to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Handle dynamic method calls to the model.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->model, $method), $parameters);
    }

}