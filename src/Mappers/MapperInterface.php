<?php namespace Entrack\RestfulAPIService\Mappers;

interface MapperInterface {

    /**
     * Map arrays input to Model attributes
     *
     * @param array $input
     * @return array
     */
    public function mapInput(array $input);

    /**
     * Map an arrays output back to input
     *
     * @param array $output
     * @return array
     */
    public function mapOutput(array $output);

    /**
     * Get the mapped key for input
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function getInputItemMapKey($key, $default = null);
    /**
     * Get the mapped key for output
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function getOutputItemMapKey($key, $default = null);

}