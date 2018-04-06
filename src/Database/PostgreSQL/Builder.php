<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL;

trait Builder {

    /**
     * Add a basic where clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if($this->isArrayColumn($column)) {
            return call_user_func_array(array($this->query, 'whereArray'), func_get_args());
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if($this->isArrayColumn($column)) {
            return call_user_func_array(array($this->query, 'whereInArray'), func_get_args());
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'and', $not = true)
    {
        if($this->isArrayColumn($column)) {
            return call_user_func_array(array($this->query, 'whereNotInArray'), func_get_args());
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    public function isArrayColumn($column)
    {
        if(!is_string($column)) {
            return false;
        }

        $model = $this->getModel();
        return array_get($model->getPostgresColumns(), last(explode('.',$column)), false);
    }

    public function search(array $search, $dictionary = 'english')
    {
        $column = $this->getModel()->getSearch();
        $sql = $column . " @@ to_tsquery('$dictionary', ?)";
        $search = array_map(
            function($s) {
                $s = trim($s);
                $s = preg_replace('/[^a-z0-9\s]/i', ' ', $s);
                return preg_replace('!\s+!', ' ', $s);
            },
            $search
        );

        $query = array_map(
            function($q) {
                return implode('&', array_map(
                    function($v) {
                        return $v.':*';
                    },
                    explode(' ', $q)
                ));
            },
            $search
        );

        $query = implode('|', $query);

        $this->whereRaw($sql, [$query]);
    }
}