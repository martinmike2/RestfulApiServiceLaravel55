<?php namespace Entrack\RestfulAPIService\Contracts;

interface EntityInterface {

    /**
     * Get the type property
     *
     * @return mixed
     */
    public function type();

    /**
     * Return all of the models attributes
     *
     * @return mixed
     */
    public function attributes();

    /**
     * Return a JsonApi formatted includes array
     *
     * @return mixed
     */
    public function relationships();

    /**
     * Returns the entity as an array
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns the results of a method or model property
     * if it exists
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property);

}