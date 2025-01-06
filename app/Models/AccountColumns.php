<?php

namespace App\Models;

trait AccountColumns
{
    public function accountIdCol(): string
    {
        return $this->qualifyColumn("ACCOUNTID");
    }

    public function accountNameCol(): string
    {
        return $this->qualifyColumn("ACCOUNTNAME");
    }

    public function accountTypeCol(): string
    {
        return $this->qualifyColumn("ACCOUNTTYPE");
    }

    public function accountNumCol(): string
    {
        return $this->qualifyColumn("ACCOUNTNUM");
    }

    public function statusCol(): string
    {
        return $this->qualifyColumn("STATUS");
    }

    public function notesCol(): string
    {
        return $this->qualifyColumn("NOTES");
    }

    public function heldatCol(): string
    {
        return $this->qualifyColumn("HELDAT");
    }

    public function websiteCol(): string
    {
        return $this->qualifyColumn("WEBSITE");
    }

    public function contactInfoCol(): string
    {
        return $this->qualifyColumn("CONTACTINFO");
    }

    public function accessInfoCol(): string
    {
        return $this->qualifyColumn("ACCESSINFO");
    }

    public function initialBalanceCol(): string
    {
        return $this->qualifyColumn("INITIALBAL");
    }

    public function initialDateCol(): string
    {
        return $this->qualifyColumn("INITIALDATE");
    }

    public function favoriteAccountCol(): string
    {
        return $this->qualifyColumn("FAVORITEACCT");
    }

    public function currencyIdCol(): string
    {
        return $this->qualifyColumn("CURRENCYID");
    }

    public function statementLockedCol(): string
    {
        return $this->qualifyColumn("STATEMENTLOCKED");
    }

    public function statementDateCol(): string
    {
        return $this->qualifyColumn("STATEMENTDATE");
    }

    public function minimumBalanceCol(): string
    {
        return $this->qualifyColumn("MINIMUMBALANCE");
    }

    public function creditLimitCol(): string
    {
        return $this->qualifyColumn("CREDITLIMIT");
    }

    public function interestRateCol(): string
    {
        return $this->qualifyColumn("INTERESTRATE");
    }

    public function paymentDueDateCol(): string
    {
        return $this->qualifyColumn("PAYMENTDUEDATE");
    }

    public function minimumPaymentCol(): string
    {
        return $this->qualifyColumn("MINIMUMPAYMENT");
    }
}
