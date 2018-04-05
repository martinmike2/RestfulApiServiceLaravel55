<?php namespace Entrack\RestfulAPIService\Entities;

trait EloquentToEntityTrait {

    /**
     * @param null $entity
     * @return mixed
     * @throws EntityNotFoundException
     */
    public function toEntity($entity = null)
    {
        return static::createEntityFromModel(
            $this,
            $entity ?: $this->defaultEntityClass()
        );
    }

    /**
     * Create a new entity from an Eloquent model
     *
     * @param Model $model
     * @param $entity
     * @return mixed
     * @throws EntityNotFoundException
     */
    private static function createEntityFromModel($model, $entity)
    {
        if(!class_exists($entity))
        {
            throw new Exceptions\EntityNotFoundException('Entity not found: ' . $entity);
        }

        return new $entity($model);
    }

    /**
     * Return the namespace for a default Entity class
     * based on instance model name
     *
     * @return mixed
     */
    protected function defaultEntityClass()
    {
        $class = get_class($this);
        $base = class_basename($class);

        return substr_replace($class, $base.'Entity', strrpos($class, $base));
    }

    /**
     * Create a new Entity Collection instance.
     *
     * @param  array  $models
     * @return \Entrack\RestfulAPIService\Entities\EloquentToEntityCollection
     */
    public function newCollection(array $models = array())
    {
        return new EloquentToEntityCollection($models);
    }

}