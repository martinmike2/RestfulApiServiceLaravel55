<?php namespace Entrack\RestfulAPIService\Entities\Pagination;

use \Illuminate\Pagination\Paginator as IlluminatePaginator;

class Paginator extends IlluminatePaginator {

    use PaginatorEntityCollectionTrait;

}