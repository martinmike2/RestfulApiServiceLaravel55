<?php namespace Entrack\RestfulAPIService\HttpQuery\Eloquent;

use Illuminate\Support\Collection;

trait PaginateBuilderTrait
{

    /**
     * Is the instance paginated
     *
     * @var bool
     */
    protected $paginated = false;

    /**
     * Return and instance of paginator if paginate scope query
     * is paginated
     *
     * @param  array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = array('*'))
    {
        $collection = parent::get($columns);

        return $this->getPaginated() ? $this->paginateCollection($collection) : $collection;

    }

    /**
     * Set the paginated var
     *
     * @param $bool
     */
    public function setPaginated($bool)
    {
        $this->paginated = $bool !== false ? true : false;
    }

    /**
     * Get the paginated var
     *
     * @return bool
     */
    public function getPaginated()
    {
        return $this->paginated;
    }

    /**
     * Paginate a collection of models
     * and return a paginator instance
     *
     * @param Collection $collection
     * @return mixed
     */
    public function paginateCollection(Collection $collection)
    {
        list($page, $per_page) = array_values($this->getPaginateParams());
        $total = $this->getPaginationCount();

        $paginator = $this->getPaginator();
        $paginator->setCurrentPage($page);

        return $paginator->make($collection->all(), $total, $per_page);
    }

    /**
     * The the pagination parameters
     * from the global scope
     *
     * @return array
     */
    public function getPaginateParams()
    {
        return $this->model->getGlobalScope(
            new \Entrack\RestfulAPIService\HttpQuery\Eloquent\Scopes\PaginateScope([])
        )->getPaginate();
    }

    /**
     * Get the query paginator instance
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function getPaginator()
    {
        return $this->query->getConnection()->getPaginator();
    }

    /**
     * Get the pagination count
     *
     * @return int
     */
    public function getPaginationCount()
    {
        return $this->query->getPaginationCount();
    }
}