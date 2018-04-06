<?php namespace Entrack\RestfulAPIService\Http\Client;

use GuzzleHttp\Client;
use Illuminate\Support\MessageBag;

class JsonApiClient implements ClientInterface {

    /**
     * The Guzzle Client instance
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The Api Connection Instance
     *
     * @var Connections\Connection
     */
    protected $connection;

    /**
     * The parameters to attach to
     * a Guzzle request
     *
     * @var array
     */
    protected $params = [];

    /**
     * The headers to attach to the
     * Guzzle request
     *
     * @var array
     */
    protected $headers;

    public function __construct(Client $client, $connection, $headers = array())
    {
        $this->client = $client;
        $this->connection = $connection;
        $this->headers = $headers;
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
        return $this->catchException(function() use($path, $query) {
            $this->setQuery($query);
            $results = $this->client->get($path, $this->generateParams());
            return $this->formatResults($results);
        });
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
        return $this->catchException(function() use($path, $body) {
            $this->setBody($body);
            $results = $this->client->post($path, $this->generateParams());
            return $this->formatResults($results);
        });
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
        return $this->catchException(function() use($path, $body) {
            $this->setBody($body);
            $results = $this->client->put($path, $this->generateParams());
            return $this->formatResults($results);
        });
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
       return $this->catchException(function() use($path, $body) {
            $this->setBody($body);
            $results = $this->client->patch($path, $this->generateParams());
            return $this->formatResults($results);
        });
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
        return $this->catchException(function() use($path, $body) {
            $this->setBody($body);
            return $this->client->delete($path, $this->generateParams());
        });
    }

    /**
     * Get all of the parameters for the client request
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get a param from the client request
     *
     * @param $key
     * @return null
     */
    public function getParam($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * Set a param to be added to the client request
     *
     * @param $key
     * @param $value
     */
    public function setParam($key, $value)
    {
        if(is_null($value) && isset($this->params[$key]))
        {
            unset($this->params[$key]);
        }

        $this->params[$key] = $value;
    }

    /**
     * Get the query parameter
     *
     * @return mixed
     */
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * Set the query parameter
     *
     * @param array $query
     */
    public function setQuery(array $query = [])
    {
        if(!empty($query))
        {
            $this->setParam('query', $query);
        }
    }

    /**
     * Get the body parameter
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->getParam('json');
    }

    /**
     * Set the body parameter
     *
     * @param array $body
     */
    public function setBody(array $body = [])
    {
        if(!empty($body))
        {
            $this->setParam('json', $body);
        }
    }

    /**
     * Get the request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the request headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * Add a request header
     *
     * @param $key
     * @param $value
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Generate the parameters for a guzzle request
     *
     * @return mixed
     */
    public function generateParams()
    {
        return array_merge(
            [
                'headers' => $this->getHeaders()
            ],
            $this->getParams()
        );
    }

    public function formatResults($results)
    {
        return json_decode((string) $results->getBody(), true);
    }

    /**
     * Catch Guzzle ClientExceptions
     *
     * @param \Closure $closure
     * @return MessageBag
     */
    public function catchException(\Closure $closure)
    {
        try {

            return $closure();

        } catch(\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            if($response->getStatusCode() !== 422) {
                throw $e;
            }

            $message = ['message' => array_get($response->json(), 'detail')];
            $errors = array_get($response->json(), 'errors');

            return new MessageBag(array_merge($message, $errors));
        }
    }
}