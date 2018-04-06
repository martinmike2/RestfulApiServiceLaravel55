<?php namespace Entrack\RestfulAPIService\Presenters;

use Entrack\RestfulAPIService\Contracts\EntityInterface;
use Entrack\RestfulAPIService\Contracts\FormatPresenterInterface;
use Illuminate\Http\Request;

class JsonApiFormatPresenter implements FormatPresenterInterface
{
    protected $entity;

    protected $request;

    public function __construct(EntityInterface $entity, Request $request)
    {
        $this->entity = $entity;
        $this->request = $request;
    }

    public static function format(EntityInterface $entity)
    {
        $presenter = new static($entity, app('request'));

        return [
            'links' => $presenter->requestUrl(),
            'data' => $presenter->data(),
            'included' => $presenter->includes()
        ];
    }

    public function data()
    {
        return array_merge(
            ['type' => $this->entity->type()],
            $this->mapAndQuoteLargeIntegers($this->entity->getAttributes()),
            $this->links()
        );
    }

    public function requestUrl()
    {
        $query = $this->request->getQueryString();
        $path = url()->current();

        return [
            'self' => $query ? "$path?$query" : $path
        ];
    }

    public function includes()
    {
        $relationships = array_map(
            function($value, $key) {
                return array_map(function ($r) use ($key) {
                    return ['type' => $key]
                        + static::mapAndQuoteLargeIntegers($r)
                        + ['links' => [
                            'self' => "/$key/{$r['id']}"
                        ]];
                }, $value);
            },
            $this->entity->relationships()
        );

        return array_flatten($relationships);
    }

    public function links()
    {
        $id = $this->entity->id;
        $type = $this->entity->type();

        return [
            'links' => array_merge(
                $this->selfLink($type, $id),
                $this->relationshipLinks($type, $id, $this->entity->relationships)
            )
        ];
    }

    public static function selfLink($type, $id)
    {
        return ['self' => "/$type/$id"];
    }

    public static function relationshipLinks($type, $id, array $relationships)
    {
        return array_map(
            function($value, $key) use ($type, $id) {
                return [
                    'related' => "$type/$id/$key",
                    'linkage' => array_map(function($value) use ($key) {
                        return [
                            'type' => $key,
                            'id' => static::quoteLargeInteger($value['id'])
                        ];
                    }, $value)
                ];
            },
            $relationships
        );
    }

    public static function mapAndQuoteLargeIntegers($array)
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return static::mapAndQuoteLargeIntegers($value);
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                return static::mapAndQuoteLargeIntegers($value->toArray());
            }

            return static::quoteLargeInteger($value);
        }, $array);
    }

    public static function quoteLargeInteger($value)
    {
        if (is_int($value) && strlen($value) > 10) {
            return "$value";
        }

        return $value;
    }
}