<?php

namespace App\Models;

trait TransactionColumns
{
    public function transactionIdCol() { return $this->qualifyColumn("TRANSID"); }
    public function fromAccountIdCol() { return $this->qualifyColumn("ACCOUNTID"); }
    public function toAccountIdCol() { return $this->qualifyColumn("TOACCOUNTID"); }
    public function payeeIdCol() { return $this->qualifyColumn("PAYEEID"); }
    public function transactionTypeCol() { return $this->qualifyColumn("TRANSCODE"); }
    public function transactionAmountCol() { return $this->qualifyColumn("TRANSAMOUNT"); }
    public function statusCol() { return $this->qualifyColumn("STATUS"); }
    public function transactionNumberCol() { return $this->qualifyColumn("TRANSACTIONNUMBER"); }
    public function notesCol() { return $this->qualifyColumn("NOTES"); }
    public function categoryIdCol() { return $this->qualifyColumn("CATEGID"); }
    public function transactionDateCol() { return $this->qualifyColumn("TRANSDATE"); }
    public function lastUpdatedTimeCol() { return $this->qualifyColumn("LASTUPDATEDTIME"); }
    public function deletedTimeCol() { return $this->qualifyColumn("DELETEDTIME"); }
    public function followUpIdCol() { return $this->qualifyColumn("FOLLOWUPID"); }
    public function toTransAmountCol() { return $this->qualifyColumn("TOTRANSAMOUNT"); }
    public function colorCol() { return $this->qualifyColumn("COLOR"); }
}
