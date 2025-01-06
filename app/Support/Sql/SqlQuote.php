<?php

namespace App\Support\Sql;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlQuote implements Expression, Stringable
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

    public function __toString(): string
    {
        return "'{$this->expression}'";
    }
}
