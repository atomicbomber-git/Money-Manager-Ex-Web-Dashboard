<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use TransactionColumns;

    protected $table = "CHECKINGACCOUNT_V1";
    protected $primaryKey = "TRANSID";
}
