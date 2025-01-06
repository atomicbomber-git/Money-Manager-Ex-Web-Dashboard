<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionCategory extends Model
{
    protected $table = "CATEGORY_V1";
    public $primaryKey = "CATEGID";

    public function categoryIdCol(): string
    {
        return $this->qualifyColumn('CATEGID');
    }

    public function categoryNameCol(): string
    {
        return $this->qualifyColumn('CATEGNAME');
    }

    public function activeCol(): string
    {
        return $this->qualifyColumn('ACTIVE');
    }

    public function parentIdCol(): string
    {
        return $this->qualifyColumn('PARENTID');
    }


    public function parent(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, "PARENTID");
    }
}
