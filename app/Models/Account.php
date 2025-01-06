<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use CanBeAliased;
    use AccountColumns;

    protected $table = "ACCOUNTLIST_V1";
    protected $primaryKey = "ACCOUNTID";
}
