<?php namespace Entrack\RestfulAPIService\Entities\Pagination;

use Entrack\RestfulAPIService\Entities\EloquentToEntityCollection;

trait PaginatorEntityCollectionTrait {

    /**
     * Get a collection instance containing the items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollection()
    {
        return new EloquentToEntityCollection($this->items);
    }

    public function toEntity()
    {
        $this->items = $this->getCollection()->toEntity()->all();

        return $this;
    }

}