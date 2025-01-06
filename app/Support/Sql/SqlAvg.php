<?php

namespace App\Support\Sql;


use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlAvg implements Expression, Stringable
{
    private string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function getValue(Grammar $grammar)
    {
        return (string) $this;
    }

    public function __toString()
    {
        return "AVG($this->expression)";
    }
}
