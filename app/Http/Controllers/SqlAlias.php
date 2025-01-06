<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;

class SqlAlias implements Expression
{
    /**
     * @var mixed|string|Expression
     */
    private mixed $expression;
    private string $alias;

    /**
     * @param Expression|string $expression
     * @param string $alias
     */
    public function __construct($expression, string $alias)
    {
        $this->expression = $expression;
        $this->alias = $alias;
    }

    public function getValue(Grammar $grammar)
    {
        return "$this->expression AS $this->alias";
    }
}
