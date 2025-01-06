<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;


class WithdrawalByCategoryController extends Controller
{
    public function indexPage()
    {
        return Inertia::render("WithdrawalByCategoryIndex");
    }

    public function index(Request $request)
    {
        $timeFrom = Carbon::parse($request->get("time_from"));
        $timeTo = Carbon::parse($request->get("time_to"));

        $transactionCategoryModel = new TransactionCategory();
        $transactionModel = new Transaction();
        $transactionAggModel = (new Transaction())->setTable("transaction_agg");

        return JsonResource::collection(
            TransactionCategory::query()
                ->select([
                    new SqlAlias($transactionCategoryModel->getQualifiedKeyName(), "category_id"),
                    new SqlAlias($transactionCategoryModel->categoryNameCol(), "category_name"),
                    new SqlAlias($transactionCategoryModel->activeCol(), "active"),
                    new SqlAlias($transactionCategoryModel->parentIdCol(), "category_parent_id"),
                    new SqlAlias(new SqlCoalesce($transactionAggModel->qualifyColumn("total"), 0), "total"),
                    new SqlAlias(new SqlCoalesce($transactionAggModel->qualifyColumn("average"), 0), "average"),
                ])
                ->joinSub(
                    $transactionModel
                        ->select([
                            new SqlAlias($transactionModel->qualifyColumn('CATEGID'), 'category_id'),
                            new SqlAlias(
                                new SqlSum($transactionModel->qualifyColumn('TRANSAMOUNT')),
                                "total"
                            ),
                            new SqlAlias(
                                new SqlAvg($transactionModel->qualifyColumn('TRANSAMOUNT')),
                                "average"
                            )
                        ])
                        ->where(
                            $transactionModel->qualifyColumn('TRANSCODE'),
                            'Withdrawal'
                        )
                        ->where(
                            $transactionModel->qualifyColumn('DELETEDTIME'),
                            ''
                        )
                        ->where(
                            $transactionModel->qualifyColumn('TRANSDATE'),
                            '>=',
                            $timeFrom->format("Y-m-d")
                        )
                        ->where(
                            $transactionModel->qualifyColumn('TRANSDATE'),
                            '<',
                            $timeTo->format("Y-m-d")
                        )
                        ->groupBy($transactionModel->qualifyColumn('CATEGID')),
                    $transactionAggModel->getTable(),
                    $transactionAggModel->qualifyColumn('category_id'),
                    $transactionCategoryModel->qualifyColumn('CATEGID'),
                )
                ->get()
        );
    }
}
