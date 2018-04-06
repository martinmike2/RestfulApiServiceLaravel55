<?php namespace Entrack\RestfulAPIService\HttpQuery;

class HttpQueryParser {

    /**
     * Run Parser methods on array
     *
     * @param array $query
     * @return mixed
     */
    public static function parse(array $query)
    {
        return HttpQueryParser::expand(
            HttpQueryParser::sanitizeArrayKeys($query)
        );
    }

    /**
     * Expand comma separated array values to arrays
     *
     * @param $array
     * @return mixed
     */
    public static function expand($array)
    {
        $values = array_map(
            function($v)
            {
                return is_array($v)
                    ? HttpQueryParser::expand($v)
                    : HttpQueryParser::parseListString($v);
            },
            $array
        );

        return array_combine(array_keys($array), $values);
    }

    /**
     * Clean array keys of unsanitized values
     *
     * @param $array
     * @return mixed
     */
    public static function sanitizeArrayKeys($array)
    {
        $keys = array_map(
            function($k)
            {
                return is_int($k)
                    ? $k
                    : HttpQueryParser::replaceWithUnderscores(
                        HttpQueryParser::stripQuotes($k)
                    );
            },
            array_keys($array)
        );

        $values = array_map(
            function($v)
            {
                return is_array($v)
                    ? HttpQueryParser::sanitizeArrayKeys($v)
                    : $v;
            },
            array_values($array)
        );

        return array_combine($keys, $values);
    }

    /**
     * Strip extra quotes from a string
     *
     * @param $str
     * @return mixed
     */
    public static function stripQuotes($str)
    {
        return preg_replace('/(^[\"\']|[\"\']$)/', '', $str);
    }

    /**
     * Replace slashes, dashes, and spaces to underscores
     *
     * @param $str
     * @return mixed
     */
    public static function replaceWithUnderscores($str)
    {
        return str_replace(['/','-', ' '], '_', $str);
    }

    /**
     * Convert comma separated values to arrays
     *
     * @param $list
     * @return mixed
     */
    public static function parseListString($list)
    {
        return is_null($list) ? $list : preg_split('/ ?, ?/', $list);
    }
}