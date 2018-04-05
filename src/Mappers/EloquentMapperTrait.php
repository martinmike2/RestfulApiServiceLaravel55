<?php namespace Entrack\RestfulAPIService\Mappers;

trait EloquentMapperTrait
{
    /**
     * Mapper Instance
     *
     * @var \Entrack\RestfulAPIService\Mappers\ResourceMapperInterface
     */
    protected $mapper;

    /**
     * Get the fillable attributes of a given array.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        return parent::fillableFromArray($this->mapInput($attributes));
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @return bool|int
     */
    public function update(array $attributes = array())
    {
        return parent::update($this->mapOutput($attributes));
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return parent::getAttribute(
            $this->getInputKey($key)
        );
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        return parent::getAttributeFromArray(
            $this->getInputKey($key)
        );
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        return $this->mapOutput(parent::attributesToArray());
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->mapOutput(parent::getAttributes());
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool   $sync
     * @return void
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        parent::setRawAttributes(
            $this->mapInput($attributes),
            $sync
        );
    }

    /**
     * Get the model's original attribute values.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return array
     */
    public function getOriginal($key = null, $default = null)
    {
        return parent::getOriginal(
            $this->getInputKey($key),
            $default
        );
    }

    /**
     * Map all keys for output
     *
     * @param array $attributes
     * @return mixed
     * @throws Exceptions\MapperNotFoundException
     */
    protected function mapOutput(array $attributes = array())
    {
        return $this->getDefaultMapper()->mapOutput($attributes);
    }

    /**
     * Map all keys for input
     *
     * @param array $attributes
     * @return mixed
     * @throws Exceptions\MapperNotFoundException
     */
    protected function mapInput(array $attributes = array())
    {
        return $this->getDefaultMapper()->mapInput($attributes);
    }

    /**
     * Get the mapped key for input
     *
     * @param $key
     * @return mixed
     * @throws Exceptions\MapperNotFoundException
     */
    protected function getInputKey($key)
    {
        return $this->getDefaultMapper()->getInputItemMapKey($key, $key);
    }

    /**
     * Get the mapped key for output
     *
     * @param $key
     * @return mixed
     * @throws Exceptions\MapperNotFoundException
     */
    protected function getOutputKey($key)
    {
        return $this->getDefaultMapper()->getOutputItemMapKey($key, $key);
    }

    /**
     * Return the namespace for a default Mapper class
     * based on instance model name
     *
     * @return mixed
     */
    protected function defaultMapperClass()
    {
        $class = get_class($this);
        $base = class_basename($class);

        return substr_replace($class, $base.'Mapper', strrpos($class, $base));
    }

    /**
     * Create a new entity from an Eloquent model
     *
     * @return \Entrack\RestfulAPIService\Mappers\ResourceMapperInterface
     * @throws \Entrack\RestfulAPIService\Mappers\Exceptions\MapperNotFoundException
     */
    private function getDefaultMapper()
    {
        $mapper = $this->defaultMapperClass();

        if($this->mapper instanceof $mapper) {
            return $this->mapper;
        }

        if(!class_exists($mapper))
        {
            throw new Exceptions\MapperNotFoundException('Mapper not found: ' . $mapper);
        }

        return new $mapper;
    }

}