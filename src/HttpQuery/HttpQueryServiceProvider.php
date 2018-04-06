<?php namespace Entrack\RestfulAPIService\HttpQuery;

use Illuminate\Support\ServiceProvider;
use Entrack\RestfulAPIService\HttpQuery\HttpQuery;

class HttpQueryServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Set the singleton Instance within the IOC Container
        app()->singleton('\Entrack\RestfulAPIService\HttpQuery\HttpQuery', function()
        {
            return new HttpQuery(null, null, null, null, null, null);
        });
    }

}