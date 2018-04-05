<?php namespace Entrack\RestfulAPIService\Mappers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EloquentEntityRelationshipMapper {

    /**
     * Instance of Model
     *
     * @var \Illuminate\Database\Eloquent\Model $model
     */
    protected $model;

    /**
     * The model relationship array
     *
     * @return array
     */
    protected $relations;

    /**
     * Class constructor
     *
     * @param \Illuminate\Database\Eloquent\Model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->relations = array_except($model->getRelations(), ['pivot']);
    }

    /**
     * Create a new mapper instance
     *
     * @param \Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function make($model)
    {
        return new static($model);
    }

    /**
     * Gets both nested and primary relationships
     *
     * @return array
     */
    public function all()
    {
        return array_merge($this->primary(), $this->nested());
    }

    /**
     * Get the primary relationship array
     *
     * @return array
     */
    public function primary()
    {
        return array_merge(
            $this->getRelationsAsArray(),
            $this->getNullRelationships()
        );
    }

    /**
     * Gets the nested relationships array
     *
     * @return array
     */
    public function nested()
    {
        $relations = [];

        foreach($this->filteredRelations() as $relation => $data)
        {
            foreach($this->createCollectionInstance($data) as $nested)
            {
                $relations = array_merge_recursive(
                    $relations,
                    $this->prependArrayKeys(
                        $this->make($nested)->primary(),
                        $relation
                    )
                );
            }
        }

        return $relations;
    }


    protected function prependArrayKeys(array $nested, $prepend, $sep = '.')
    {
        return array_combine(
            array_map(
                function($key) use($prepend,$sep)
                {
                    return $prepend.$sep.$key;
                },
                array_keys($nested)
            ),
            $nested
        );
    }

    /**
     * Gets the relationships attributes as and array
     *
     * @return mixed
     */
    protected function getRelationsAsArray()
    {
        $relations = $this->filteredRelations();

        return array_combine(
            array_keys($relations),
            array_map(
                [$this, 'getModelRelationshipAttributes'],
                $relations
            )
        );
    }

    protected function filteredRelations()
    {
        return array_filter(
            $this->relations,
            function($relation)
            {
                return !is_null($relation);
            }
        );
    }

    protected function getNullRelationships()
    {
        $null = array_except(
            $this->relations,
            array_keys($this->filteredRelations())
        );

        return array_combine(
            array_keys($null),
            array_map(
                function()
                {
                    return [];
                },
                $null
            )
        );
    }

    /**
     * Returns the attributes array for a model
     * or collection of models
     *
     * @param $relationship Model or Collection
     * @return array
     */
    protected function getModelRelationshipAttributes($relationship)
    {
        return $this->createCollectionInstance($relationship)
            ->map(
                function($model)
                {
                    return $model->toEntity()->attributes();
                }
            )
            ->toArray();
    }

    /**
     * Creates a new instance of collection
     *
     * @param $items
     * @return Collection
     */
    protected function createCollectionInstance($items)
    {
        return $items instanceof Collection ? $items : new Collection([ $items ]);
    }
}