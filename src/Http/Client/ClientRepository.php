<?php namespace Entrack\RestfulAPIService\Http\Client;

use Entrack\RestfulAPIService\Http\Client\Models\Model;
use Entrack\RestfulAPIService\Http\Client\Connections\Connection;
use Illuminate\Support\MessageBag;

class ClientRepository implements ClientInterface {

    protected $connection;

    protected $model;

    /**
     * Instantiate the repository
     *
     * @param Connection $connection
     * @param Model $model
     */
    public function __construct(Connection $connection, Model $model)
    {
        $this->connection = $connection;
        $this->model = $model;
    }

    /**
     * Http GET request
     *
     * @param string $path
     * @param array $query
     * @return mixed
     */
    public function get($path, $query = [])
    {
        $results = $this->connection->get($path, $query);
        return $this->newModelFromResults($results);
    }

    /**
     * Http POST request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function post($path, $body = [])
    {
        $results = $this->connection->post($path, $body);
        return $this->newModelFromResults($results);
    }

    /**
     * Http PUT request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function put($path, $body = [])
    {
        $results = $this->connection->put($path, $body);
        return $this->newModelFromResults($results);
    }

    /**
     * Http PATCH request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function patch($path, $body = [])
    {
        $results = $this->connection->patch($path, $body);
        return $this->newModelFromResults($results);
    }

    /**
     * Http DELETE request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function delete($path, $body = [])
    {
        $results = $this->connection->delete($path, $body);
        return $this->newModelFromResults($results);
    }

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
        $model = $this->model->newInstance($data);

        if($included = array_get((array) $results, 'included')) {
            $model->newQuery()->setRelations($model, $included);

        }

        return $model;
    }
}