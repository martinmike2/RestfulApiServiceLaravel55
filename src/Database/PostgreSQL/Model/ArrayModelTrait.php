<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Model;

use Fuelingbrands\PostgreSQL\Relations\HasManyArray;
use Fuelingbrands\PostgreSQL\Relations\HasManyInArray;
use Fuelingbrands\PostgreSQL\Helpers\ArrayHelper as Arr;

trait ArrayModelTrait {

    /**
     * Stored Array Attributes
     *
     * @var array
     */
    public $array_attributes = [];

    /**
     * Return the Json columns from the
     * postgres Column array
     *
     * @return mixed
     */
    public function getArrayColumns()
    {
        return array_keys($this->getPostgresColumns(), 'array');
    }

    /**
     * Decodes each of the declared JSON attributes and records the attributes
     * on each
     *
     * @return void
     */
    public function inspectArrayColumns()
    {
        foreach ($this->getArrayColumns() as $col)
        {
            $this->flagArrayAttributesByColumn($col);
        }
    }

    /**
     * Flag an object of Json Attributes
     *
     * @param string $col
     * @return void
     */
    public function flagArrayAttributesByColumn($col)
    {
        $this->flagArrayAttribute($col, $col);
    }

    /**
     * Record that a given Array element is found on a particular column
     *
     * @param string $key
     * @param string $column
     * @return void
     */
    public function flagArrayAttribute($key, $column)
    {
        $this->array_attributes[$key] = $column;
    }

    /**
     * Check if an attributes exists within
     * array_attributes property
     *
     * @param $key
     */
    protected function arrayAttributeExists($key)
    {
        return array_key_exists($key, $this->array_attributes);
    }

    /**
     * Include the Array attributes in the list of mutated attributes for a
     * given instance.
     *
     * @return array
     */
    public function getArrayMutatedAttributes()
    {
        return array_keys($this->array_attributes);
    }

    /**
     * Check if the key is a known json attribute and return that value
     *
     * @todo: This only really works for 1-level deep. Should it be more?
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function mutateArrayAttribute($key, $value)
    {
        if($this->arrayAttributeExists($key))
        {
            return $this->getArrayAttribute($key);
        }

        return false;
    }

    /**
     * Return an array attribute
     *
     * @param $key
     * @return mixed
     */
    protected function getArrayAttribute($key)
    {
        $column = array_get($this->getArrayAttributes(), $key);
        return Arr::stringToArray($this->getAttributeFromArray($column));
    }

    /**
     * Get the array_attributes property
     *
     * @return array
     */
    protected function getArrayAttributes()
    {
        return $this->array_attributes;
    }

    /**
     * Define a one-to-many relationship from postgres array column.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Fuelingbrands\PostgreSQL\Relations\HasManyArray
     */
    public function hasManyArray($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = new $related;

        $localKey = $localKey ?: $this->getKeyName();

        return new HasManyArray($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship from postgres array column.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Fuelingbrands\PostgreSQL\Relations\HasManyArray
     */
    public function hasManyInArray($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = new $related;

        $localKey = $localKey ?: $this->getKeyName();

        return new HasManyInArray($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }
}