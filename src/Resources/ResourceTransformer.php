<?php namespace Entrack\RestfulAPIService\Resources;

use Entrack\RestfulAPIService\Contracts\EntityInterface;
use Illuminate\Support\Facades\Input;
use Entrack\RestfulAPIService\Presenters\JsonApiFormatPresenter;


class ResourceTransformer
{
    public $currentScope;

    public function getAvailableIncludes()
    {
        return [];
    }

    public function getDefaultIncludes()
    {
        return [];
    }

    /**
     * Setter for currentScope.
     *
     * @param Scope $currentScope
     *
     * @return $this
     */
    public function setCurrentScope($currentScope)
    {
        $this->currentScope = $currentScope;

        return $this;
    }

    public function format(EntityInterface $entity)
    {
        return JsonApiFormatPresenter::format($entity);
    }

    /**
     * Check if item is in include get parameter
     *
     * @param $include
     * @return mixed
     */
    public function is_included($include)
    {
        return in_array(
            (string) $include,
            $this->includes()
        );
    }

    /**
     * Return the include parameter as an array
     *
     * @return mixed
     */
    public function includes()
    {
        return explode(',', Input::get('include', ''));
    }
}