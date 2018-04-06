<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Model;

trait JsonModelTrait {

    /**
     * List of known Postgres JSON operators
     *
     * @var array
     */
    public static $json_operators = [
        '->',
        '->>',
        '#>',
        '#>>'
    ];

    /**
     * Stored Json Attributes
     *
     * @var array
     */
    public $json_attributes = [];


    /**
     * Return the Json columns from the
     * postgres Column array
     *
     * @return mixed
     */
    public function getJsonColumns()
    {
        return array_keys($this->getPostgresColumns(), 'json');
    }

    /**
     * Decodes each of the declared JSON attributes and records the attributes
     * on each
     *
     * @return void
     */
    public function inspectJsonColumns()
    {
        foreach ($this->getJsonColumns() as $col)
        {
            $this->hideColumn($col);
            $this->flagJsonAttributesByColumn($col);
        }
    }

    /**
     * Add a new column to thehidden column property
     *
     * @param $column
     */
    protected function hideColumn($column)
    {
        $this->hidden[] = $column;
    }

    /**
     * remove a column from the hidden column property
     *
     * @param $column
     */
    protected function hiddenColumnToVisible($column)
    {
        $key = array_search($column, $this->hidden);

        if($key !== false) {
            $hidden = $this->hidden;
            unset($hidden[$key]);
            $this->hidden = $hidden;
        }
    }

    /**
     * Flag an object of Json Attributes
     *
     * @param string $col
     * @return void
     */
    public function flagJsonAttributesByColumn($col)
    {
        $attributes = json_decode($this->getAttributeFromArray($col)) ?: [];

        if(is_array($attributes)) {
            $this->hiddenColumnToVisible($col);
            $this->flagJsonAttribute($col, $col);
            return;
        }

        foreach ($attributes as $key => $value) {
            $this->flagJsonAttribute($key, $col);
            $this->appendKey($key);
        }

    }

    /**
     * Record that a given JSON element is found on a particular column
     *
     * @param string $key
     * @param string $column
     * @return void
     */
    public function flagJsonAttribute($key, $column)
    {
        $this->json_attributes[$key] = $column;
    }

    /**
     * Append a new key to the appends property
     *
     * @param string $key
     */
    protected function appendKey($key)
    {
        $this->appends[] = $key;
    }

    /**
     * Check if an attributes exists within
     * json_attributes property
     *
     * @param $key
     */
    protected function jsonAttributeExists($key)
    {
        return array_key_exists($key, $this->json_attributes);
    }

    /**
     * Check if key matches a known json operator
     *
     * @param string $key
     * @return bool
     */
    protected function jsonPatternMatch($key)
    {
        return preg_match($this->jsonPattern(), $key);
    }

    /**
     * Return a regex pattern string for json operators
     *
     * @return string
     */
    protected function jsonPattern()
    {
        return '/' . implode('|', self::$json_operators) . '/' ;
    }

    /**
     * Include the JSON attributes in the list of mutated attributes for a
     * given instance.
     *
     * @return array
     */
    public function getJsonMutatedAttributes()
    {
        return array_keys($this->json_attributes);
    }

    /**
     * Check if the key is a known json attribute and return that value
     *
     * @todo: This only really works for 1-level deep. Should it be more?
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function mutateJsonAttribute($key, $value)
    {
        $json = false;

        if ($this->jsonPatternMatch($key))
        {
            $key = $this->reduceNestedAttribute($key);
            $json = true;
        }

        $exists = $this->jsonAttributeExists($key);
        if($exists && !in_array($key, array_keys($this->attributes)))
        {
            return $this->getJsonAttribute($key);
        }
        else if($exists) {
            return $this->getJsonColumn($key);
        }
        else if($json === true) {
            return null;
        }

        return false;
    }

    /**
     * Reduce nested attribute to end key
     *
     * @param $key
     * @return string
     */
    protected function reduceNestedAttribute($key)
    {
        $key = last(
            preg_split($this->jsonPattern(), $key)
        );

        return str_replace([">", "'"], "", $key);
    }

    /**
     * Return json column a json decoded object
     *
     * @param $col
     * @return mixed
     */
    protected function getJsonColumn($col)
    {
        return json_decode($this->attributes[$col]);
    }

    /**
     * Return a nested json attribute
     *
     * @param $key
     * @return mixed
     */
    protected function getJsonAttribute($key)
    {
        if (is_object($obj = $this->{$this->json_attributes[$key]})) {
            return $obj->$key;
        }

        $obj = json_decode($this->{$this->json_attributes[$key]});
        return $obj->$key;
    }

    /**
     * Replace the json attributes for a column
     *
     * @param $key
     * @return mixed
     */
    protected function setJsonAttributes($key, $value)
    {
        $this->attributes[$key] = json_encode($value);
        $this->inspectJsonColumns();
    }

    /**
     * Get the json_attributes property
     *
     * @return array
     */
    protected function getJsonAttributes()
    {
        return $this->json_attributes;
    }

    /**
     * Set a given attribute on the known JSON elements.
     *
     * @param string $attribute
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function setJsonAttribute($attribute, $key)
    {
        if(!$this->jsonAttributeExists($key))
        {
            return false;
        }

        $obj = json_decode($this->{$attribute});
        $obj->$key = $value;
        $this->flagJsonAttribute($key, $attribute);
        $this->{$attribute} = json_encode($obj);

        return true;
    }
}