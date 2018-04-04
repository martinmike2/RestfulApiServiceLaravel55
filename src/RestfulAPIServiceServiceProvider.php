<?php

namespace Entrack\RestfulAPIService;

use Dingo\Api\Auth\Provider\JWT;
use Dingo\Api\Transformer\Adapter\Fractal;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

class RestfulAPIServiceServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'../config/api.php' => config_path('api.php')
        ]);

        $app = App();
        $app['Dingo\Api\Auth\Auth']->extend('oauth', function ($app) {
            return new JWT($app['Tymon\JWTAuth\JWTAuth']);
        });

        $app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {
            $fractal = new Manager();
            $fractal->setSerializer(new JsonApiSerializer());
            return new Fractal($fractal);
        });

        $app['Dingo\Api\Exception\Handler']->setErrorFormat([
            'error' => [
                'message' => ':message',
                'errors' => ':errors',
                'code' => ':code',
                'status_code' => ':status_code',
                'debug' => ':debug'
            ]
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

    }
}