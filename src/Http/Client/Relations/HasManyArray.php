<?php namespace Entrack\RestfulAPIService\Http\Client\Relations;

use Entrack\PostgreSQL\Relations\HasManyArrayTrait;

class HasManyArray extends HasMany {

    use HasManyArrayTrait {
        HasManyArrayTrait::getKeys as hasManyArrayTraitGetKeys;
    }

    /**
     * Converts all array attributes back to string
     * when getting keys
     *
     * @param  array   $models
     * @param  string  $key
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        $keys = $this->hasManyArrayTraitGetKeys($models, $key);

        return str_replace(['{','}'], '', $keys);
    }

}