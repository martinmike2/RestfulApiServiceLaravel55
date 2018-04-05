<?php namespace Entrack\RestfulAPIService\Mappers;

class Mapper implements MapperInterface {

    /**
     * Course Input => Model Mapper
     *
     * @var array
     */
    public $map = [];

    /**
     * Map arrays input to Model attributes
     *
     * @param array $input
     * @return array
     */
    public function mapInput(array $input)
    {
        return ArrayKeyMapper::mapArrayKeys($input,$this->map);
    }

    /**
     * Map an arrays output back to input
     *
     * @param array $output
     * @return array
     */
    public function mapOutput(array $output)
    {
        return ArrayKeyMapper::mapArrayKeys($output, array_flip($this->map));
    }


    /**
     * Get the mapped key for input
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function getInputItemMapKey($key, $default = null)
    {
        return array_key_exists($key,$this->map)
            ?$this->map[$key]
            : $default;
    }

    /**
     * Get the mapped key for output
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function getOutputItemMapKey($key, $default = null)
    {
        return array_search($key) ?: $default;
    }

}