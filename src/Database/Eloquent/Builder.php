<?php namespace Entrack\RestfulAPIService\Database\Eloquent;

use Entrack\RestfulAPIService\HttpQuery\Eloquent\FieldsBuilderTrait;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Entrack\RestfulAPIService\HttpQuery\Eloquent\PaginateBuilderTrait;
use Entrack\RestfulAPIService\Database\PostgreSQL\Builder as PostgresBuilderTrait;

class Builder extends IlluminateBuilder {

    use BuilderExceptionTrait;
    use PaginateBuilderTrait;
    use FieldsBuilderTrait;
    use PostgresBuilderTrait;

}