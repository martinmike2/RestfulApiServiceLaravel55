<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent;

trait FieldsBuilderTrait
{
    /**
     * Set the fields to columnize
     *
     * @var array
     */
    protected $fields;

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function getModels($columns = array('*'))
    {
        $columns = $this->fields ?: $columns;

        return parent::getModels($columns);
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param  array     $models
     * @param  string    $name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function loadRelation(array $models, $name, \Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        call_user_func($constraints, $relation);

        $models = $relation->initRelation($models, $name);

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        $model = $relation->getModel();
        $table = $model->getTable();

        // Apply the global scopes for the relationship
        // Since it is only applied to an empty model
        if(method_exists($model, 'getGlobalScopes')) {
            $scopes = $model->getGlobalScopes();
            if(count($scopes)) {
                foreach($scopes as $scope) {
                    $query = $relation->getQuery();
                    $scope->apply($query);
                }
            }
        }

        // Apply the fields scope to the relation
        try {
            $fields = $model->getFields();
            $fields = array_map(
                function($field) use($table) {
                    return $table . '.' . $field;
                },
                $fields ?: ['*']
            );

            $results = $relation->get($fields);
        } catch (\BadMethodCallException $e){
            $results = $relation->getEager();
        }

        return $relation->match($models, $results, $name);
    }

    /**
     * Set the field columns to return on query
     *
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }


    /**
     * Get the fields array
     *
     * @return null
     */
    public function getFields()
    {
        return $this->fields;
    }
}