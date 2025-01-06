<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountController extends Controller
{
    private Account $accountModel;

    public function __construct(Account $accountModel)
    {
        $this->accountModel = $accountModel;
    }

    public function indexQuery()
    {
        return $this->accountModel->newQuery()
            ->select([
                new SqlAlias($this->accountModel->accountIdCol(), "account_id"),
                new SqlAlias($this->accountModel->accountNameCol(), "account_name"),
                new SqlAlias($this->accountModel->accountTypeCol(), "account_type"),
                new SqlAlias($this->accountModel->accountNumCol(), "account_num"),
                new SqlAlias($this->accountModel->statusCol(), "status"),
                new SqlAlias($this->accountModel->notesCol(), "notes"),
                new SqlAlias($this->accountModel->heldatCol(), "heldat"),
                new SqlAlias($this->accountModel->websiteCol(), "website"),
                new SqlAlias($this->accountModel->contactInfoCol(), "contact_info"),
                new SqlAlias($this->accountModel->accessInfoCol(), "access_info"),
                new SqlAlias($this->accountModel->initialBalanceCol(), "initial_balance"),
                new SqlAlias($this->accountModel->initialDateCol(), "initial_date"),
                new SqlAlias($this->accountModel->favoriteAccountCol(), "favorite_account"),
                new SqlAlias($this->accountModel->currencyIdCol(), "currency_id"),
                new SqlAlias($this->accountModel->statementLockedCol(), "statement_locked"),
                new SqlAlias($this->accountModel->statementDateCol(), "statement_date"),
                new SqlAlias($this->accountModel->minimumBalanceCol(), "minimum_balance"),
                new SqlAlias($this->accountModel->creditLimitCol(), "credit_limit"),
                new SqlAlias($this->accountModel->interestRateCol(), "interest_rate"),
                new SqlAlias($this->accountModel->paymentDueDateCol(), "payment_due_date"),
                new SqlAlias($this->accountModel->minimumPaymentCol(), "minimum_payment"),
            ])
            ->orderBy($this->accountModel->accountIdCol());
    }

    public function index()
    {
        return JsonResource::collection(
            $this->indexQuery()
                ->get()
        );
    }
}
