<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Model;

use Fuelingbrands\PostgreSQL\Relations\HasManyArray;
use Fuelingbrands\PostgreSQL\Helpers\ArrayHelper as Arr;

trait SearchModelTrait {

    /**
     * Search string as column or tsvector
     * example: to_tsvector(title || ' ' || description || ' ' || number)
     *
     * @var string
     */
    protected $search;

    /**
     * Get the search property
     *
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Set the search property
     *
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }
}