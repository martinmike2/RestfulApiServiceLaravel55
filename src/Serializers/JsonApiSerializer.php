<?php namespace Entrack\RestfulAPIService\Serializers;

use Entrack\RestfulAPIService\Transformers\CollectionResponseTransformer;
use League\Fractal\Serializer\ArraySerializer;

class JsonApiSerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        return with(new CollectionResponseTransformer())->transform($data);
    }
}