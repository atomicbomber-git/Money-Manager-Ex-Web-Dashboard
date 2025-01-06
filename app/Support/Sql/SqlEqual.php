<?php

namespace App\Support\Sql;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlEqual implements Expression, Stringable
{
    private string $leftExpression;
    private string $rightExpression;

    public function __construct(
        string $leftExpression,
        string $rightExpression,
    )
    {
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function getValue(Grammar $grammar)
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return "$this->leftExpression = $this->rightExpression";
    }
}
