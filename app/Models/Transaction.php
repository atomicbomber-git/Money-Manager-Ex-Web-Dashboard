<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use TransactionColumns;
    public const TypeWithdrawal = "Withdrawal";
    public const TypeDeposit = "Deposit";
    public const TypeTransfer = "Transfer";

    protected $table = "CHECKINGACCOUNT_V1";
    protected $primaryKey = "TRANSID";
}
