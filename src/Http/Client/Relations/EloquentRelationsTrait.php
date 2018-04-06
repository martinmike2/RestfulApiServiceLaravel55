<?php namespace Entrack\RestfulAPIService\Http\Client\Relations;

use Entrack\RestfulAPIService\Http\Client\Models\Model;

trait EloquentRelationsTrait {

    /**
     * Define a one-to-one relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;

        if($this->isClientModel($instance))
        {
            return $instance->hasOne($this, $foreignKey, $localKey);
        }

       return parent::hasOne($related, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;

        if($this->isClientModel($instance))
        {
            return $instance->hasMany($this, $foreignKey, $localKey);
        }

        return parent::hasMany($related, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyArray($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;

        if($this->isClientModel($instance))
        {
            return $instance->hasManyArray($this, $foreignKey, $localKey);
        }

        return parent::hasManyArray($related, $foreignKey, $localKey);
    }


    /**
     * Check if related instance is a client model
     *
     * @param $model
     * @return bool
     */
    public function isClientModel($model)
    {
        return $model instanceof Model;
    }
}