<?php namespace Entrack\RestfulAPIService\Transformers;

class CollectionResponseTransformer
{
    protected $original;

    protected $response;

    public function transform($response)
    {
        $this->original = $response;
        $this->setLinks();
        $this->setData();
        $this->setIncluded();
        return $this->response;
    }

    public function setLinks()
    {
        $this->response['links'] = array_get(
            head($this->original),
            'links',
            []
        );
    }

    public function setIncluded()
    {
        $return = [];
        $included = array_flatten(array_pluck($this->original, 'included'));
        $unique = array_unique(array_pluck($included, 'id'));

        foreach ($unique as $item) {
            $return[] = array_first(
                $included,
                function ($key, $value) use ($item) {
                    return $value['id'] === $item;
                }
            );
        }

        $this->response['included'] = $return;
    }

    public function setData()
    {
        $this->response['data'] = array_pluck($this->original, 'data');
    }
}