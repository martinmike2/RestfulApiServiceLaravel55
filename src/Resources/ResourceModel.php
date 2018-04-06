<?php namespace Entrack\RestfulAPIService\Resources;

use Entrack\RestfulAPIService\Http\Client\Relations\Relation;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Entrack\RestfulAPIService\Entities\EloquentToEntityTrait;
use Entrack\RestfulAPIService\HttpQuery\Eloquent\HttpQueryTrait;
use Entrack\RestfulAPIService\Mappers\EloquentMapperTrait;
use Entrack\RestfulAPIService\Database\PostgreSQL\Model\PostgresModelTrait;
use Entrack\RestfulAPIService\Database\Eloquent\BuilderModelTrait;
use Entrack\RestfulAPIService\Models\ModelObserverTrait;
use Entrack\RestfulAPIService\Http\Client\Relations\EloquentRelationsTrait;
use Laracasts\Commander\Events\EventGenerator;

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

    /**
     * Get a relationship value from a method.
     *
     * @param $method
     * @return mixed
     *
     */
    protected function getRelationshipFromMethod($method)
    {
        $relation = $this->$method();

        if (! $relation instanceof Relation) {
            throw new \LogicException("{get_class($this)}::$method must return a relationship instance.");
        }

        return tap($relation->getResults(), function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }
}