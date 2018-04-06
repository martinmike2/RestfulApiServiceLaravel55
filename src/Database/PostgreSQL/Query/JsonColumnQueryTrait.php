<?php namespace Entrack\RestfulAPIService\Database\PostgreSQL\Query;

use Illuminate\Database\Query\Expression;

/**
 * Class JsonColumnQueryTrait
 * @credit rtablada/whereJson https://github.com/rtablada/whereJson
 */
trait JsonColumnQueryTrait {

    /**
     * Return Json attribute by column
     *
     * @param $column
     * @param $value
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @param null $castType
     * @return $this
     */
    public function whereJson($column, $attribute, $operator = null, $value = null, $boolean = 'and', $castType = null)
    {
        $column = $this->buildColumn($column, $attribute, $castType);

        if (!$value)
        {
            $value = $operator;
            $operator = '=';
        }

        return $this->whereRaw("{$column} {$operator} ?", [$value], $boolean);
    }

    /**
     * Or Query for WhereJson
     *
     * @param $column
     * @param $attribute
     * @param null $operator
     * @param null $value
     * @param null $castType
     * @return JsonQueryBuilder
     */
    public function orWhereJson($column, $attribute, $operator = null, $value = null, $castType = null)
    {
        return $this->whereJson($column, $attribute, $operator, $value, 'or', $castType);
    }

    public function select($columns = array('*'))
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $key => $value) {
            $columns[$key] = $this->prepareColumnValue($value);
        }
        $this->columns = $columns;
        return $this;
    }

    public function andSelect($columns = array('*'))
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $key => $value) {
            $columns[$key] = $this->prepareColumnValue($value);
        }
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    protected function prepareColumnValue($value)
    {
        if (preg_match('/->/', $value)) {
            $phrases = explode(' ', $value);
            foreach ($phrases as $key => $phrase) {
                $pieces = explode('->', $phrase);
                $i = 1;
                for ($i; $i < count($pieces); $i++) {
                    $pieces[$i] = "'{$pieces[$i]}'";
                }
                $phrases[$key] = implode('->', $pieces);
            }
            return new Expression(implode(' ', $phrases));
        }
        return $value;
    }

    protected function buildColumn($column, $columnTraverse, $castType)
    {
        $columnTraverse = explode('->', $columnTraverse);
        if (is_array($columnTraverse)) {
            foreach ($columnTraverse as $property) {
                $column = "{$column}->'{$property}'";
            }
            $column = $this->replaceLastInstanceInString('->', '->>', $column);
        } else {
            $column = "{$column}->>'{$columnTraverse}'";
        }
        $column = $castType ? "({$column})::{$castType}" : $column;
        return $column;
    }

    protected function replaceLastInstanceInString($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);
        if($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

}