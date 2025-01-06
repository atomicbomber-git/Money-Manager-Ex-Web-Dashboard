<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WithdrawalByCategoryController;
use Illuminate\Support\Facades\Route;



Route::redirect("/", "/transaction/page");
Route::get("/account", [AccountController::class, "index"])->name("account.index");
Route::get("/withdrawal-by-category/page", [WithdrawalByCategoryController::class, "indexPage"]);
Route::get("/withdrawal-by-category", [WithdrawalByCategoryController::class, "index"]);
Route::get("/transaction", [TransactionController::class, "index"]);
Route::get("/transaction/page", [TransactionController::class, "indexPage"]);
