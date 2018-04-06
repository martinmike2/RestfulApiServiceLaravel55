<?php namespace Entrack\RestfulAPIService\Http\Client\Models;

use Entrack\RestfulAPIService\Http\Client\ClientRepository;
use Illuminate\Support\MessageBag;

class ModelClientRepository extends ClientRepository {

    /**
     * Return an new instance of the Model
     *
     * @param $results
     * @return Model
     */
    protected function newModelFromResults($results)
    {
        if($results instanceof MessageBag) {
            return $results;
        }

        $data = array_get((array) $results, 'data');
        $included = array_get((array) $results, 'included');
        $models = $this->model->newCollection();

        foreach($data as $model) {
            $model = $this->model->newInstance($model);

            if($included) {
                $model->newQuery()->setRelations($model, $included);
            }

            $models->put($model->id, $model);
        }

        return $models;
    }

}