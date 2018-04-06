<?php

namespace Entrack\RestfulAPIService\Database;

use \DB as DB,
    \Exception as Exception,
    \File as File,
    \Schema as Schema;

class Import
{

    /**
     * Object initializer.
     */
    public function __construct(){}

    /**
     * Verifies the existence of the specified table.
     * Handles the importation of CSV data.
     *
     * @param string $table
     * @param string $columns
     * @param string $file
     * @return boolean
     */
    public function csv($table, $columns, $file)
    {
        $columns = $this->columns($columns);
        $file    = $this->file($file);
        $table   = $this->table($table);

        return DB::unprepared("COPY $table($columns) FROM '$file' WITH DELIMITER AS ',' CSV HEADER;");
    }

    /**
     * Check if any column data exists within the $data param.
     * Convert all column names into a comma separated string.
     *
     * @param array $data
     * @return string
     */
    private function columns($data)
    {
        $columns = [];

        try {

            if(count($data)) {

                foreach ($data as $key => $value) {
                    if(array_key_exists('mapping', $value)) {
                        $columns[$key] = $key;
                    }
                }

            } else {
                throw new Exception('No columns were provided or are available.');
            }

        } catch(Exception $e) {
            print $e->getMessage() . "\n";
        }

        return implode(', ', $columns);
    }

    /**
     * Check whether the specified data file exists.
     *
     * @param string $file
     * @return boolean
     */
    private function file($file)
    {
        try {

            if(File::exists($file)) {
               return $file;
            } else {
                throw new Exception('Import data file (' . $file . ') was not found or does not exist.');
            }

        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Check whether the specified table exists.
     *
     * @param string $table
     * @return boolean
     */
    private function table($table)
    {
        try {

            if(Schema::hasTable($table)) {
                return $table;
            } else {
                throw new Exception('The specified data import table (' . $table . ') was not found or does not exist.');
            }

        } catch(Exception $e) {
            return false;
        }
    }

}
