<?php namespace Entrack\RestfulAPIService\Tests;


use Dingo\Api\Http\Response;
use Entrack\RestfulAPIService\Transformers\CollectionResponseTransformer;

class CollectionResponseTransformerTest extends TestCase
{
    public function testConstructs()
    {
        $class = new CollectionResponseTransformer();

        $this->assertInstanceOf(CollectionResponseTransformer::class, $class);
    }

    public function testTransforms()
    {
        $response = new Response([]);
        $class = new CollectionResponseTransformer();

        $class->transform($response);

        dd($class);
    }
}