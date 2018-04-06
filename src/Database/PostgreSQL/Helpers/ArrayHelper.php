<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Helpers;

class ArrayHelper {

    /**
     * Convert an array to postgresql array string
     *
     * @param array $array
     * @return string
     */
    public static function arrayToString(array $array)
    {
        return '{' . implode(',', $array) . '}';
    }

    /**
     * Remove postgres array formatting
     *
     * @param $string
     * @return mixed
     */
    public static function trimArrayString($string)
    {
        return substr($string, 1, -1);
    }

    /**
     * Explode a postgres array string
     *
     * @param $string
     * @return mixed
     */
    public static function stringToArray($string)
    {
        return explode(',', static::trimArrayString($string));
    }

    /**
     * Check if string is a postgres array
     *
     * @param $string
     * @return bool
     */
    public static function isArray($string)
    {
        preg_match('/^{(.*)}$/', $string, $matches);

        return count($matches) ? true : false;
    }

}