<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Merchant\BankAccountController;
use App\Http\Controllers\Api\V1\Merchant\EmployeeController;
use App\Http\Controllers\Api\V1\Merchant\MerchantController;
use App\Http\Controllers\Api\V1\Transaction\TransactionController;
use App\Http\Controllers\Api\V1\Webhook\BankWebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
    });

    // Webhooks (no auth, token-based)
    Route::post('webhooks/bank/{token}', [BankWebhookController::class, 'handle']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // Transactions
        Route::get('transactions',              [TransactionController::class, 'index']);
        Route::get('transactions/stats',        [TransactionController::class, 'stats']);
        Route::get('transactions/{uuid}',       [TransactionController::class, 'show']);
        Route::post('transactions/{uuid}/verify',[TransactionController::class, 'verify']);

        // Merchant
        Route::get('merchant/profile',   [MerchantController::class, 'profile']);
        Route::put('merchant/profile',   [MerchantController::class, 'update']);
        Route::get('merchant/epvs-id',   [MerchantController::class, 'epvsIdCard']);

        // Bank Accounts
        Route::get('merchant/bank-accounts',          [BankAccountController::class, 'index']);
        Route::post('merchant/bank-accounts',         [BankAccountController::class, 'store']);
        Route::delete('merchant/bank-accounts/{uuid}',[BankAccountController::class, 'destroy']);

        // Employees
        Route::get('employees',                          [EmployeeController::class, 'index']);
        Route::post('employees',                         [EmployeeController::class, 'store']);
        Route::put('employees/{uuid}/permissions',       [EmployeeController::class, 'updatePermissions']);
        Route::delete('employees/{uuid}',                [EmployeeController::class, 'destroy']);
    });
});
