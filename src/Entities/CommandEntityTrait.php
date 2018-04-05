<?php namespace Entrack\RestfulAPIService\Entities;

trait CommandEntityTrait {

    /**
     * Converts a timestamp string to ISO 8601 standard
     *
     * @return string
     */
    protected function convertTimestampFormat($timestamp)
    {
        return date('Y-m-d H:i:s', strtotime($timestamp));
    }

    /**
     * Returns all public properties of the class
     *
     * @return array
     */
    public function getPublicProperties()
    {
        return array_keys(
            call_user_func('get_object_vars', $this)
        );
    }

    /**
     * Maps public class properties to input values
     *
     * @param array $input
     * @return void
     */
    protected function mapInputArrayToProperties(array $input)
    {
        $properties = $this->intersectArrayPropertyValues($input);

        foreach($properties as $property => $value)
        {
            $this->{$property} = $value;
        }
    }

    /**
     * Return all inputs that have a corresponding
     * public property
     *
     * @param array $input
     * @return array
     */
    public function intersectArrayPropertyValues(array $input)
    {
        return array_intersect_key(
            $input,
            array_flip(
                $this->getPublicProperties()
            )
        );
    }

    /**
     * Get the instance as an array
     * without extended attributes
     *
     * @return array
     */
    public function toArrayRaw()
    {
        $self = $this;
        $properties = $this->getPublicProperties();

        return array_combine(
            $properties,
            array_map(
                function($property) use ($self)
                {
                    return $self->{$property};
                },
                $properties
            )
        );
    }

    /**
     * Get the instance as an array
     * with extended attributes
     *
     * @return array
     */
    public function toArray()
    {
        $self = $this;
        $properties = $this->getPublicProperties();

        return $this->filterEmptyOrNull(
            array_combine(
                $properties,
                array_map(
                    function($property) use ($self)
                    {
                        return $self->__get($property);
                    },
                    $properties
                )
            )
        );
    }

    /**
     * Remove empty or null properties from array
     *
     * @param $array
     * @return mixed
     */
    public function filterEmptyOrNull($array)
    {
        return array_filter(
            $array,
            function($value)
            {
                return !is_null($value) || $value === false;
            }
        );
    }

    /**
     * Return the property method or the
     * property if method does not exist
     *
     * @return mixed
     */
    public function __get($property)
    {
        if(method_exists($this, camel_case($property)))
        {
            return $this->{camel_case($property)}();
        }

        return $this->$property;
    }

}