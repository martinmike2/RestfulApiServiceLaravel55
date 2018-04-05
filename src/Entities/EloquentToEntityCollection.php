<?php namespace Entrack\RestfulAPIService\Entities;

use Illuminate\Database\Eloquent\Collection;


class EloquentToEntityCollection extends Collection {

    /**
     * Convert a collection of models to entities
     *
     * @return $this
     */
    public function toEntity()
    {
        return $this->transform(
            function($model)
            {
                return $model->toEntity();
            }
        );
    }

    /**
     * Run the illuminate collection
     * to array method
     *
     * @return array
     */
    public function parentToArray()
    {
        return parent::toArray();
    }

    /**
     * Return entity as an array
     *
     * @return array
     */
    public function toArray()
    {
        $clone = $this->make($this->all());

        $clone->transform(
            function($entity)
            {
               return $entity instanceof Contracts\EntityInterface
                   ? $entity->toArray()
                   : $entity;
            }
        );

        return $clone->parentToArray();
    }
}