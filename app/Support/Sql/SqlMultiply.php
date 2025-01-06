<?php

namespace App\Support\Sql;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlMultiply implements Expression, Stringable
{
    private array $expressions;

    public function __construct(...$expressions)
    {
        $this->expressions = $expressions;
    }

    public function getValue(Grammar $grammar)
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return implode(" * ", $this->expressions);
    }
}
