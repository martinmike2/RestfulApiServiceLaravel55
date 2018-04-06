<?php namespace Entrack\RestfulAPIService\Http\Client\Models;

interface ModelInterface {

    /**
     * Return all from resource
     *
     * @param array $fields
     * @return mixed
     */
    public static function all($fields = ['*']);

    /**
     * Find an individual resource by Id
     *
     * @param $id
     * @param array $fields
     * @return mixed
     */
    public static function find($id, $fields = ['*']);

    /**
     * Create a new resource
     *
     * @param array $attributes
     * @return mixed
     */
    public static function create($attributes = []);

    /**
     * Update an existing resource
     *
     * @param array $attributes
     * @return mixed
     */
    public static function update($attributes = []);

    /**
     * Delete an existing resource
     *
     * @param int $id
     * @return mixed
     */
    public static function delete($id);

}