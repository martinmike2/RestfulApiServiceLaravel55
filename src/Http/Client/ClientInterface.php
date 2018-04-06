<?php namespace Entrack\RestfulAPIService\Http\Client;

interface ClientInterface {

    /**
     * Http GET request
     *
     * @param string $path
     * @param array $query
     * @return mixed
     */
    public function get($path, $query = []);

    /**
     * Http POST request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function post($path, $body = []);

    /**
     * Http PUT request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function put($path, $body = []);

    /**
     * Http PATCH request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function patch($path, $body = []);

    /**
     * Http DELETE request
     *
     * @param string $path
     * @param array $body
     * @return mixed
     */
    public function delete($path, $body = []);

}