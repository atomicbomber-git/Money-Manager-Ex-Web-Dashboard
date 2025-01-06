<?php

namespace App\Support\Sql;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlCoalesce implements Expression, Stringable
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

    public function __toString()
    {
        return "COALESCE(" .  implode(", ", $this->expressions) . ")";
    }
}
