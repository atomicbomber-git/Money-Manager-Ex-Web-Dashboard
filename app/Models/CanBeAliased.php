<?php

namespace App\Models;

use App\QueryBuilders\Alias;
use App\Support\Sql\SqlAlias;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

trait CanBeAliased
{
    private ?string $aliasedTable = null;
    private string $originalTable = '';

    private static int $aliasCounter = 2;

    public function withAlias(?string $alias = null): self
    {
        if (!$this->originalTable) {
            $this->originalTable = $this->getTable();
        }

        $this->aliasedTable = $alias ?? $this->originalTable . '_' . static::$aliasCounter++;

        $this->table = $this->aliasedTable;
        return $this;
    }

    public function newQuery(): Builder
    {
        return $this->aliasedTable ?
            parent::newQuery()->from($this->aliasExpression()) :
            parent::newQuery();
    }

    public function getTableAlias(): string
    {
        return $this->aliasedTable;
    }

    public function aliasExpression(): Expression
    {
        return new Expression(
            $this->aliasedTable === null
                ? $this->getTable()
                : new SqlAlias($this->originalTable, $this->aliasedTable)
        );
    }
}

