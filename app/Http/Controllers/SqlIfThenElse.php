<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlIfThenElse implements Expression, Stringable
{
    private string $conditionExpression;
    private string $whenTrueExpression;
    private string $whenFalseExpression;

    public function __construct(
        string $conditionExpression,
        string $whenTrueExpression,
        string $whenFalseExpression
    )
    {
        $this->conditionExpression = $conditionExpression;
        $this->whenTrueExpression = $whenTrueExpression;
        $this->whenFalseExpression = $whenFalseExpression;
    }

    public function getValue(Grammar $grammar)
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return "IIF($this->conditionExpression, $this->whenTrueExpression, $this->whenFalseExpression)";
    }
}
