<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Model;

use Entrack\RestfulAPIService\Database\PostgreSQL\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Builder;

//use Entrack\RestfulAPIService\Database\PostgreSQL\Exceptions;

trait PostgresModelTrait
{
    use ArrayModelTrait;
    use JsonModelTrait;
    use SearchModelTrait;

    /**
     * Boot the Postgres Model Trait.
     *
     * @return void
     */
    public static function bootPostgresModelTrait()
    {
        // Dispatch the trait on boot
        static::$dispatcher->listen(
            'eloquent.booted: '.get_called_class(),
            function($model)
            {
                $model->inspectPostgresColumns();
            }
        );
    }

    /**
     * Override the default connection if one does not exist.
     *
     * @return void
     */
    public function getConnection()
    {
        if (!is_null($this->connection)) {
            $container = new \Illuminate\Database\Connectors\ConnectionFactory(new \Illuminate\Container\Container);
            return $container->make(\Config::get('database.connections.' . $this->connection));
        }

        return parent::getConnection();
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();
        $grammar = $conn->getQueryGrammar();

        return new Builder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Execute all column type inspect methods
     *
     * @return null
     */
    public function inspectPostgresColumns()
    {
        $this->executeTypeMethods('inspect', 'Columns');
    }

    /**
     * Get an array of the column types
     * associated with the model
     *
     * @return array
     */
    public function getPostgresColumnTypes()
    {
        return array_unique($this->getPostgresColumns());
    }


    /**
     * Get an array of all the postgres columns
     * and their types
     *
     * @return array
     */
    public function getPostgresColumns()
    {
        return property_exists($this, 'psql_columns') ? $this->psql_columns : [];
    }

    /**
     * Check all column types for a flagged attribute
     *
     * @param $key
     * @return mixed
     */
    public function postgresAttributeExists($key)
    {
        return $this->executeTypeMethods(null, 'AttributeExists', [$key], true);
    }

    /**
     * Check for postgres expression for each type
     *
     * @param $key
     * @return mixed
     */
    public function postgresPatternMatch($key)
    {
        return $this->executeTypeMethods(null, 'PatternMatch', [$key], true);
    }

    /**
     * Get the postgres mutated attribute
     *
     * @param $key
     * @param $value
     * @return bool|mixed
     */
    public function postgresMutatedAttribute($key, $value)
    {
        $attribute = $this->executeTypeMethods('mutate', 'Attribute', func_get_args(), 'any');
        return $attribute === true
            ? false
            : $attribute;
    }

    /**
     * Execute type methods dynamically with option to return on
     * specific outcome
     *
     * @param null $prefix
     * @param null $suffix
     * @param array $args
     * @param null $return_on
     * @return mixed
     */
    protected function executeTypeMethods($prefix = null, $suffix = null, $args = [], $return_on = null)
    {
        foreach($this->getPostgresColumnTypes() as $type)
        {
            $method = $this->generateMethodName([$prefix, $type, $suffix]);
            $output = $this->executeDynamicMethod($method, $args);

            if(!is_null($return_on) && $output === $return_on || $return_on === 'any' && $output !== false)
            {
                return $output;
            }
        }

        return null;
    }

    /**
     * Generate a studly case method name by array
     *
     * @param array $array
     * @param string $sep
     * @return string
     */
    protected function generateMethodName(array $array, $sep = '_')
    {
        return studly_case(
            implode($sep, array_filter($array))
        );
    }

    /**
     * Check if method exists and execute
     *
     * @param $method
     * @param array $args
     * @return mixed
     */
    protected function executeDynamicMethod($method, $args = [])
    {
        if(method_exists($this, $method)) {
            return call_user_func_array([$this,$method], $args);
        }

        return null;
    }

}