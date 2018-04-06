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
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->save($options);
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