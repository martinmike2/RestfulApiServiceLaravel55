<?php namespace Entrack\RestfulAPIService\Resources;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Fuelingbrands\RestfulApiService\Entities\EloquentToEntityTrait;
use Fuelingbrands\RestfulApiService\HttpQuery\Eloquent\HttpQueryTrait;
use Fuelingbrands\RestfulApiService\Mappers\EloquentMapperTrait;
use Fuelingbrands\PostgreSQL\Model\PostgresModelTrait;
use Fuelingbrands\RestfulApiService\Database\Eloquent\BuilderModelTrait;
use Fuelingbrands\RestfulApiService\Models\ModelObserverTrait;
use Fuelingbrands\RestfulApiService\Http\Client\Relations\EloquentRelationsTrait;
use Laracasts\Commander\Events\EventGenerator;
use Fuelingbrands\RestfulApiService\Http\Client\Relations\Relation as ClientRelation;

class ResourceModel extends Eloquent {

    use EventGenerator;
    use EloquentToEntityTrait;
    use EloquentMapperTrait;
    use BuilderModelTrait;
    use HttpQueryTrait;
    use ModelObserverTrait;
    use PostgresModelTrait
    {
        PostgresModelTrait::hasManyArray as PostgresHasManyArray;
    }
    use EloquentRelationsTrait
    {
        EloquentRelationsTrait::hasManyArray as ClientHasManyArray;
    }
    use HasAttributes;

    /**
     * Get the unguarded state
     *
     * @return bool
     */
    public static function getUnguardState()
    {
        return static::$unguarded;
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
            return $this->ClientHasManyArray($related, $foreignKey, $localKey);
        }

        return $this->PostgresHasManyArray($related, $foreignKey, $localKey);
    }
}