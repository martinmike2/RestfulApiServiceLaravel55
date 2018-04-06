<?php namespace Entrack\RestfulAPIService\Database\Eloquent;

use Entrack\RestfulAPIService\Database\Eloquent\Exceptions\RelationshipNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait BuilderExceptionTrait {

    /**
     * Find a model by its primary key and return
     * and exception if it does not exist
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     * @throws \Exception
     */
    public function find($id, $columns = array('*'))
    {
        if(!$return = parent::find($id, $columns) )
        {
            throw new NotFoundHttpException('Unable to locate requested entity');
        }

        return $return;
    }

    /**
     * Verify methods exist for an array of relationships
     *
     * @param array $relationships
     */
    public function verifyRelationshipMethods($relationships)
    {
        foreach($relationships as $relationship)
        {
            if($this->modelMethodExists($relationship) || count(explode('.', $relationship)) > 1) continue;

            throw new RelationshipNotFoundException($relationship .' relationship does not exist');
        }
    }

    /**
     * Check if method exists on model
     *
     * @param $method
     * @return boolean
     */
    public function modelMethodExists($method)
    {
        return method_exists($this->model, $method);
    }

    /**
     * Check that method exists for any
     * eager loaded relationship
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->verifyRelationshipMethods(
            array_keys($this->getEagerLoads())
        );

        return parent::__call($method, $parameters);
    }

}