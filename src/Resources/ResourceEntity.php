<?php namespace Entrack\RestfulAPIService\Resources;

use Entrack\RestfulAPIService\Entities\Contracts\EntityInterface;
use Entrack\RestfulAPIService\Entities\EntityTrait;

abstract class ResourceEntity implements EntityInterface {

    use EntityTrait;

}