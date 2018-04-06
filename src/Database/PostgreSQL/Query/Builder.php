<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Query;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Builder as IlluminateBuilder;

class Builder extends IlluminateBuilder {

    use JsonColumnQueryTrait;
    use ArrayColumnQueryTrait;

}