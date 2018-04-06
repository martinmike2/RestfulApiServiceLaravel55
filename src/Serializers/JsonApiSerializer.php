<?php namespace Entrack\RestfulAPIService\Serializers;

use Entrack\RestfulAPIService\Transformers\CollectionResponseTransformer;
use League\Fractal\Serializer\ArraySerializer;

class JsonApiSerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        return with(new CollectionResponseTransformer())->transform($data);
    }

    public function item($resource_key, array $data)
    {
        return $data;
    }

    public function meta(array $meta)
    {
        if (empty($meta)) {
            return [];
        }

        return $meta;
    }
}