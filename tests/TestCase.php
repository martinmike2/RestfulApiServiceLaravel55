<?php namespace Entrack\RestfulAPIService\Tests;

use Entrack\RestfulAPIService\RestfulAPIServiceServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            RestfulAPIServiceServiceProvider::class
        ];
    }
}
