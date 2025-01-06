<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payee;
use App\Models\Transaction;
use App\Support\Sql\SqlAlias;
use App\Support\Sql\SqlCase;
use App\Support\Sql\SqlEqual;
use App\Support\Sql\SqlIfThenElse;
use App\Support\Sql\SqlNegate;
use App\Support\Sql\SqlQuote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\HigherOrderWhenProxy;
use Inertia\Inertia;


class TransactionController extends Controller
{
    private Transaction $transactionModel;
    private Account $accountFromModel;
    private Account $accountToModel;
    private Payee $payeeModel;

    public function __construct(
        Transaction $transactionModel,
        Account     $accountFromModel,
        Account     $accountToModel,
        Payee       $payeeModel,
    )
    {
        $this->transactionModel = $transactionModel;
        $this->accountFromModel = $accountFromModel->withAlias("accounts_from");
        $this->accountToModel = $accountToModel->withAlias("accounts_to");
        $this->payeeModel = $payeeModel;
    }

    public function indexPage()
    {
        return Inertia::render("TransactionIndex");
    }

    public function index(Request $request)
    {
        $type = $request->get("type");
        $search = $request->get("search");
        $categoryId = $request->get("category_id");
        $timeFrom = $request->get("time_from") ? Carbon::parse($request->get("time_from")) : null;
        $timeTo = $request->get("time_to") ? Carbon::parse($request->get("time_to")) : null;
        $sortColumn = $request->get("sort_column");
        $sortIsAscending = (boolean)$request->get("sort_is_ascending");
        $payeeId = $request->get("payee_id");
        $accountId = $request->get("account_id");
        $accountFromId = $request->get("account_from_id");
        $accountToId = $request->get("account_to_id");


        $query = $this->query($accountId, $search, $accountFromId, $accountToId, $payeeId, $type, $categoryId, $timeFrom, $timeTo, $sortColumn, $sortIsAscending);


        return JsonResource::collection(
            $query
                ->paginate()
        );
    }

    /**
     * @param mixed $accountId
     * @param mixed $search
     * @param mixed $accountFromId
     * @param mixed $accountToId
     * @param mixed $payeeId
     * @param mixed $type
     * @param mixed $categoryId
     * @param Carbon|null $timeFrom
     * @param Carbon|null $timeTo
     * @param mixed $sortColumn
     * @param bool $sortIsAscending
     * @return Transaction|HigherOrderWhenProxy
     */
    public function query(mixed $accountId, mixed $search, mixed $accountFromId, mixed $accountToId, mixed $payeeId, mixed $type, mixed $categoryId, ?Carbon $timeFrom, ?Carbon $timeTo, mixed $sortColumn, bool $sortIsAscending): Builder
    {
        return $this->transactionModel
            ->select([
                new SqlAlias($this->transactionModel->transactionIdCol(), 'transaction_id'),
                new SqlAlias($this->accountFromModel->accountNameCol(), 'account_from'),
                new SqlAlias($this->accountFromModel->accountIdCol(), 'account_from_id'),
                new SqlAlias($this->accountToModel->accountNameCol(), 'account_to'),
                new SqlAlias($this->accountToModel->accountIdCol(), 'account_to_id'),
                new SqlAlias($this->transactionModel->transactionAmountCol(), 'amount'),
                new SqlAlias($this->transactionModel->transactionDateCol(), 'date'),
                new SqlAlias($this->payeeModel->qualifyColumn('PAYEENAME'), 'payee'),
                new SqlAlias($this->transactionModel->notesCol(), 'notes'),

                new SqlAlias(
                    $this->relativeAmountExpression($accountId),
                    "relative_amount"
                )
            ])
            ->leftJoin(
                $this->accountFromModel->aliasExpression(),
                $this->accountFromModel->accountIdCol(),
                $this->transactionModel->fromAccountIdCol(),
            )
            ->leftJoin(
                $this->accountToModel->aliasExpression(),
                $this->accountToModel->accountIdCol(),
                $this->transactionModel->toAccountIdCol(),
            )
            ->leftJoin(
                $this->payeeModel->getTable(),
                $this->payeeModel->qualifyColumn('PAYEEID'),
                $this->transactionModel->qualifyColumn('PAYEEID'),
            )
            ->when($search, function (Builder $builder, string $search) {
                $builder->where(function (Builder $builder) use ($search) {
                    $builder->orWhere(
                        $this->accountFromModel->accountNameCol(),
                        'LIKE',
                        "%{$search}%"
                    )->orWhere(
                        $this->accountToModel->accountNameCol(),
                        'LIKE',
                        "%{$search}%"
                    )->orWhere(
                        $this->transactionModel->transactionAmountCol(),
                        'LIKE',
                        "%{$search}%"
                    )->orWhere(
                        $this->payeeModel->qualifyColumn('PAYEENAME'),
                        'LIKE',
                        "%{$search}%"
                    )->orWhere(
                        $this->transactionModel->notesCol(),
                        'LIKE',
                        "%{$search}%"
                    );
                });
            })
            ->when($accountId, function (Builder $builder, $accountId) {
                $builder->where(function (Builder $builder) use ($accountId) {
                    $builder->orWhere(
                        $this->transactionModel->accountIdCol(),
                        $accountId
                    )->orWhere(
                        $this->transactionModel->qualifyColumn('TOACCOUNTID'),
                        $accountId
                    );
                });
            })
            ->when($accountFromId, function (Builder $builder, $accountFromId) {
                $builder->where(
                    $this->transactionModel->accountIdCol(),
                    $accountFromId
                );
            })
            ->when($accountToId, function (Builder $builder, $accountToId) {
                $builder->where(
                    $this->transactionModel->qualifyColumn('TOACCOUNTID'),
                    $accountToId
                );
            })
            ->when($payeeId, function (Builder $builder, $payeeId) {
                $builder->where(
                    $this->transactionModel->qualifyColumn('PAYEEID'),
                    $payeeId
                );
            })
            ->when($type, function (Builder $builder, $type) {
                $builder->where(
                    $this->transactionModel->qualifyColumn('TRANSCODE'),
                    $type
                );
            })
            ->when($categoryId, function (Builder $builder, $categoryId) {
                $builder->where(
                    $this->transactionModel->qualifyColumn('CATEGID'),
                    $categoryId
                );
            })
            ->where(
                $this->transactionModel->qualifyColumn('DELETEDTIME'),
                ""
            )
            ->when($timeFrom, function (Builder $builder, $timeFrom) {
                $builder->where(
                    $this->transactionModel->transactionDateCol(),
                    '>=',
                    $timeFrom->format("Y-m-d")
                );
            })
            ->when($timeTo, function (Builder $builder, $timeTo) {
                $builder->where(
                    $this->transactionModel->transactionDateCol(),
                    '<',
                    $timeTo->format("Y-m-d")
                );
            })
            ->orderBy([
                "transaction_id" => $this->transactionModel->transactionIdCol(),
                "account_from" => $this->accountFromModel->accountNameCol(),
                "account_to" => $this->accountToModel->accountNameCol(),
                "amount" => $this->transactionModel->transactionAmountCol(),
                "date" => $this->transactionModel->transactionDateCol(),
                "payee" => $this->payeeModel->qualifyColumn('PAYEENAME'),
                "notes" => $this->transactionModel->notesCol(),
                "relative_amount" => $this->relativeAmountExpression($accountId),
            ][$sortColumn] ?? $this->transactionModel->transactionIdCol(), $sortIsAscending ? "ASC" : "DESC"
            );
    }

    /**
     * @param mixed $accountId
     * @return SqlIfThenElse
     */
    public function relativeAmountExpression(mixed $accountId): SqlIfThenElse
    {
        $typeCol = $this->transactionModel->transactionTypeCol();
        $amountCol = $this->transactionModel->transactionAmountCol();

        $case = new SqlCase([
            (string) new SqlEqual($typeCol, new SqlQuote(Transaction::TypeWithdrawal)) => new SqlNegate($amountCol),
            (string) new SqlEqual($typeCol, new SqlQuote(Transaction::TypeDeposit)) => $amountCol,
            (string) new SqlEqual($typeCol, new SqlQuote(Transaction::TypeTransfer)) => new SqlNegate($amountCol),
        ], 0);

        return new SqlIfThenElse(
            new SqlEqual(
                $this->accountToModel->accountIdCol(),
                ($accountId ?? "NULL")
            ),
            $this->transactionModel->transactionAmountCol(),
            $case
        );
    }

}
