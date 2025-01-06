<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payee;
use App\Models\Transaction;
use App\Support\Sql\SqlAlias;
use App\Support\Sql\SqlCase;
use App\Support\Sql\SqlEqual;
use App\Support\Sql\SqlIfThenElse;
use App\Support\Sql\SqlMultiply;
use App\Support\Sql\SqlNegate;
use App\Support\Sql\SqlQuote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
        $params = $this->params($request);

        return JsonResource::collection(
            $this->indexQuery($request)
                ->paginate($params->perPage)
        );
    }

    public function params(Request $request)
    {
        return new class($request) {
            public mixed $type;
            public mixed $search;
            public mixed $categoryId;
            public ?Carbon $timeFrom;
            public ?Carbon $timeTo;
            public mixed $sortColumn;
            public bool $sortIsAscending;
            public mixed $payeeId;
            public mixed $accountId;
            public mixed $accountFromId;
            public mixed $accountToId;
            public ?int $perPage;

            public function __construct(Request $request)
            {
                $this->type = $request->get("type");
                $this->search = $request->get("search");
                $this->categoryId = $request->get("category_id");
                $this->timeFrom = $request->get("time_from") ? Carbon::parse($request->get("time_from")) : null;
                $this->timeTo = $request->get("time_to") ? Carbon::parse($request->get("time_to")) : null;
                $this->sortColumn = $request->get("sort_column");
                $this->sortIsAscending = (boolean)$request->get("sort_is_ascending");
                $this->payeeId = $request->get("payee_id");
                $this->accountId = $request->get("account_id");
                $this->accountFromId = $request->get("account_from_id");
                $this->accountToId = $request->get("account_to_id");
                $this->perPage = $request->get("per_page", 100);
            }
        };
    }

    public function relativeAmountExpression(mixed $accountId)
    {
        $typeCol = $this->transactionModel->transactionTypeCol();
        $amountCol = $this->transactionModel->transactionAmountCol();

        return $accountId === null ?
            $amountCol :
            new SqlMultiply(
                new SqlIfThenElse(
                    new SqlEqual($this->transactionModel->fromAccountIdCol(), $accountId),
                    1,
                    -1
                ),
                new SqlCase([
                    (string)new SqlEqual($typeCol, new SqlQuote(Transaction::TypeWithdrawal)) => new SqlNegate($amountCol),
                    (string)new SqlEqual($typeCol, new SqlQuote(Transaction::TypeDeposit)) => $amountCol,
                    (string)new SqlEqual($typeCol, new SqlQuote(Transaction::TypeTransfer)) => new SqlNegate($amountCol),
                ], 0)
            );
    }

    public function indexQuery(Request $request): Builder
    {
        $params = $this->params($request);

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
                    $this->relativeAmountExpression($params->accountId),
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
                $this->transactionModel->payeeIdCol(),
            )
            ->when($params->search, function (Builder $builder, string $search) {
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
            ->when($params->accountId, function (Builder $builder, $accountId) {
                $builder->where(function (Builder $builder) use ($accountId) {
                    $builder->orWhere(
                        $this->transactionModel->fromAccountIdCol(),
                        $accountId
                    )->orWhere(
                        $this->transactionModel->toAccountIdCol(),
                        $accountId
                    );
                });
            })
            ->when($params->accountFromId, function (Builder $builder, $accountFromId) {
                $builder->where(
                    $this->transactionModel->fromAccountIdCol(),
                    $accountFromId
                );
            })
            ->when($params->accountToId, function (Builder $builder, $accountToId) {
                $builder->where(
                    $this->transactionModel->toAccountIdCol(),
                    $accountToId
                );
            })
            ->when($params->payeeId, function (Builder $builder, $payeeId) {
                $builder->where(
                    $this->transactionModel->payeeIdCol(),
                    $payeeId
                );
            })
            ->when($params->type, function (Builder $builder, $type) {
                $builder->where(
                    $this->transactionModel->transactionTypeCol(),
                    $type
                );
            })
            ->when($params->categoryId, function (Builder $builder, $categoryId) {
                $builder->where(
                    $this->transactionModel->categoryIdCol(),
                    $categoryId
                );
            })
            ->where(
                $this->transactionModel->deletedTimeCol(),
                ""
            )
            ->when($params->timeFrom, function (Builder $builder, $timeFrom) {
                $builder->where(
                    $this->transactionModel->transactionDateCol(),
                    '>=',
                    $timeFrom->format("Y-m-d")
                );
            })
            ->when($params->timeTo, function (Builder $builder, $timeTo) {
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
                "relative_amount" => $this->relativeAmountExpression($params->accountId),
            ][$params->sortColumn] ?? $this->transactionModel->transactionIdCol(), $params->sortIsAscending ? "ASC" : "DESC"
            );
    }

}
