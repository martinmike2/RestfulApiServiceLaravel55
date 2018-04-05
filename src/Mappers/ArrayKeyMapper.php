<?php namespace Entrack\RestfulAPIService\Mappers;

class ArrayKeyMapper {

    public static function mapArrayKeys(array $input, array $map)
    {
        return array_combine(
            array_map(
                function($key) use($map)
                {
                    return array_key_exists($key, $map) ? $map[$key] : $key;
                },
                array_keys($input)
            ),
            $input
        );
    }

}