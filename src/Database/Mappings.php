<?php

namespace Entrack\RestfulAPIService\Database;

use \Config as Config,
    \Exception as Exception,
    \File as File;

class Mappings
{

    /**
     * The path to the database mappings.
     *
     * @var string
     */
    protected $mappings_path = 'database.mappings';

    /**
     * The suffix for all database table test files.
     *
     * @var string
     */
    protected $table_suffix = 'TableTest';

    /**
     * The PHP file extension.
     *
     * @var string
     */
    protected $php_ext = '.php';

    /**
     * The suffix for all database seeders files.
     *
     * @var string
     */
    protected $seeder_suffix = 'Seeder';

    /**
     * Object initializer.
     */
    public function __construct() {}

    /**
     * Get all available mappings.
     *
     * @return arrau
     */
    public function all()
    {
        return File::glob(Config::get($this->mappings_path) . '*');
    }

    /**
     * Get the create a table SQL statement.
     *
     * @param $mapping
     * @return object
     */
    public function getCreateTableSQL($mapping)
    {
        $statement = 'CREATE TABLE ' . $this->table($mapping) . " (\n";
        $i = 1;

        foreach ($this->columns($mapping) as $key => $value) {
            try {

                if (array_key_exists('attributes', $value)) {
                    $statement .= "\t" . $key . ' ' . $value['attributes'] . (count($this->columns($mapping)) - 1 >= $i ? ',' : '') . "\n";

                    $i++;
                } else {
                    throw new Exception('Missing column attributes for column (' . $key . ').');
                }

            } catch (Exception $e) {
                print $e->getMessage();
                exit;
            }
        }

        $statement .= ");\n";

        return $statement;
    }

    /**
     * Get the column attributes for the specific mapping configuration.
     *
     * @param $mapping
     * @return object
     */
    public function columnAttributes($mapping)
    {
        $attributes = [];

        foreach ($this->get($mapping)['columns'] as $key => $value) {
            $attributes[] = $value['attributes'];
        }

        return $attributes;
    }

    /**
     * Get the columns for the specified mapping configuration.
     *
     * @param $mapping
     * @return object
     */
    public function columns($mapping)
    {
        return $this->get($mapping)['columns'];
    }

    /**
     * Get the columns with mappings for the specified mapping configuration.
     *
     * @param $mapping
     * @return object
     */
    public function columnMappings($mapping)
    {
        $mappings = [];

        foreach ($this->get($mapping)['columns'] as $key => $value) {
            if(array_key_exists('mapping', $value)) {
                $mappings[] = $value['mapping'];
            }
        }

        return $mappings;
    }

    /**
     * Get the columns for the specified mapping configuration.
     * If the $mapped param is set to true then only columns that contain a mapping will be returned.
     *
     * @param string $mapping
     * @param boolean $mapped
     * @return object
     */
    public function columnNames($mapping, $mapped = false)
    {
        $columns = [];

        foreach ($this->get($mapping)['columns'] as $key => $value) {

            if($mapped) {

                if(array_key_exists('mapping', $value)) {
                    $columns[] = $key;
                }

            } else {
                $columns[] = $key;
            }

        }

        return $columns;
    }

    /**
     * Verify that the specified mapping exists.
     *
     * @param $mapping
     * @return mixed
     */
    public function exists($mapping)
    {
        return File::exists($mapping);
    }

    /**
     * Get the file name of the specified mapping.
     *
     * @param $mapping
     * @return string
     */
    public function filename($mapping)
    {
        return $this->name($mapping) . $this->php_ext;
    }

    /**
     * Get the mapping configuration for the specific table.
     *
     * @param $mapping
     * @return object
     */
    public function get($mapping)
    {
        $path = $this->path($mapping);

        try {

            if($this->exists($path)) {
                $map = require $path;
                return $map;
            } else {
                throw new Exception('Cannot find mapping (' . $this->name($mapping) . ')');
            }

        } catch(Exception $e) {
            print $e->getMessage();
        }

        return false;
    }

    /**
     * Check if the specified mapping is a table test and strip out the table test string from the file name.
     *
     * @param $mapping
     * @return string
     */
    public function isTest($mapping)
    {
        return strpos($mapping, $this->table_suffix) !== false ? str_replace($this->table_suffix, '', $mapping) : $mapping;
    }

    /**
     * Check if the specified mapping is a seeder and strip out the seeder string from the file name.
     *
     * @param $mapping
     * @return string
     */
    public function isSeeder($mapping)
    {
        return strpos($mapping, $this->seeder_suffix) !== false ? str_replace($this->seeder_suffix, '', $mapping) : $mapping;
    }

    /**
     * Get the name of the specified mapping.
     * But first, check if the current mapping is a seeder or migration test.
     *
     * @param $mapping
     * @return string
     */
    public function name($mapping)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->isSeeder($this->isTest($mapping)))), '_');
    }

    /**
     * Get the complete path to the specified mapping.
     *
     * @param $mapping
     * @return string
     */
    public function path($mapping)
    {
        return Config::get($this->mappings_path) . $this->filename($mapping);
    }

    /**
     * Get the table mapping for the specified mapping configuration.
     *
     * @param $mapping
     * @return string
     */
    public function table($mapping)
    {
        return $this->get($mapping)['table'];
    }

}
