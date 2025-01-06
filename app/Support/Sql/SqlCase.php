<?php

namespace App\Support\Sql;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Stringable;

class SqlCase implements Expression, Stringable
{
    private array $caseExpressions;
    private string $elseExpression;

    public function __construct(
        $caseExpressions,
        $elseExpression
    )
    {
        $this->caseExpressions = $caseExpressions;
        $this->elseExpression = $elseExpression;
    }

    public function getValue(Grammar $grammar)
    {
        return (string) $this;
    }

    public function __toString()
    {
        $sql = "CASE \n";

        foreach ($this->caseExpressions as $whenExpression => $thenExpression) {
            $sql .= sprintf("WHEN %s THEN %s\n", $whenExpression, $thenExpression);
        }

        $sql .= sprintf(" ELSE %s END\n", $this->elseExpression);

        return $sql;
    }
}
