<?php namespace Entrack\RestfulAPIService\Http\Client\Connections;

interface ConnectionInterface {

    /**
     * The the base uri for connecting Api
     *
     * @return string
     */
    public function getBaseUrl();

}