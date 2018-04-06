<?php namespace Entrack\RestfulAPIService\Models;

trait ModelObserverTrait {

    /**
     * Boot the Model Observer Trait.
     *
     * @return void
     */
    public static function bootModelObserverTrait()
    {
        $class = static::getObserverClass();

        if(class_exists($class))
        {
            static::observe(
                static::makeObserverClassInstance($class)
            );
        }
    }

    /**
     * Get the observer class based on
     * current Model namespace
     *
     * @return string
     */
    protected static function getObserverClass()
    {
        $path = explode('\\', get_called_class());
        $class = array_pop($path) . 'Observer';

        return implode('\\', $path).'\\'.$class;
    }

    /**
     * Create a new instance of an observer class
     *
     * @param string $class
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function makeObserverClassInstance($class)
    {
        return with(new \ReflectionClass($class))
            ->newInstance();
    }
}