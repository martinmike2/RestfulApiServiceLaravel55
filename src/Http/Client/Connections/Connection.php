<?php namespace Entrack\RestfulAPIService\Http\Client\Connections;

use Entrack\RestfulAPIService\Http\Client\JsonApiClient;
use Entrack\RestfulAPIService\Http\Client\ClientInterface;

use GuzzleHttp\Client;

class Connection implements ConnectionInterface
{
    /**
     * The connection paramaters
     *
     * @var array
     */
    protected $connection;

    /**
     * The current logged in user
     *
     * @var \Illuminate\Auth\Guard
     */
    protected $auth;

    /**
     * Default headers
     *
     * @var array
     */
    protected $headers = [
        'Content-Type' => 'application/json'
    ];

    /**
     * Instatiate the connection
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->connection = \Config::get('fuel.api::connections.' . $name);
        $this->auth = app('auth')->user();
    }

    /**
     * The the base url for connecting Api
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->connection['url'];
    }

    /**
     * Create a new client instance
     *
     * @param array $headers
     * @return JsonApiClient
     */
    public function newClient($headers = [])
    {
        $client =  new JsonApiClient(
            new Client(['base_url' => $this->getBaseUrl()]),
            $this,
            $headers
        );

        $this->addHeaders($client, $this->headers);
        $this->setAcceptHeader($client);
        $this->setAuthHeader($client);

        return $client;
    }

    /**
     * Add multiple headers to the client request
     *
     * @param ClientInterface $client
     * @param array $headers
     */
    public function addHeaders(ClientInterface $client, array $headers)
    {
        foreach($headers as $header => $value)
        {
            $this->addHeader($client, $header, $value);
        }
    }

    /**
     * Add a header to the client request
     *
     * @param ClientInterface $client
     * @param $header
     * @param $value
     */
    public function addHeader(ClientInterface $client, $header, $value)
    {
        $client->addHeader($header, $value);
    }

    /**
     * Set the Accept header
     *
     * @param ClientInterface $client
     */
    public function setAcceptHeader(ClientInterface $client)
    {
        $vendor = array_get($this->connection, 'vendor', 'api');
        $version = array_get($this->connection, 'version', 'v1');
        $value = 'application/vnd.'.implode('.', [$vendor, $version]) . '+json';

        $this->addHeader($client, 'Accept', $value);
    }

    /**
     * Set the auth header
     *
     * @param ClientInterface $client
     */
    public function setAuthHeader(ClientInterface $client)
    {
        $auth = $this->auth;
        if(!is_null($auth)) {
            $this->addHeader($client, 'Authorization', $auth->getAuthorizationType() . ' ' . $auth->getAccessToken());
        }
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $client = $this->newClient();

        return call_user_func_array(array($client, $method), $parameters);
    }
}