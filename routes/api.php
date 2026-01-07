<?php


use App\Http\Controllers\API;


Route::prefix('v1')->group(function () {
    Route::middleware(['idempotency'])->group(function () {
        Route::post('wallets/{id}/deposit', [API\WalletController::class, 'deposit'])->name('wallets.deposit');
        Route::post('wallets/{id}/withdraw', [API\WalletController::class, 'withdraw'])->name('wallets.withdraw');
        Route::post('wallets/{id}/transfer', [API\WalletController::class, 'transfer'])->name('wallets.transfer');
    });
    Route::get('health', [API\BaseApiController::class, 'health'])->name('health');
    Route::get('wallets/{id}/balance', [API\WalletController::class, 'balance'])->name('wallets.balance');
    Route::apiResource('wallets', API\WalletController::class)->names('wallets');
    Route::apiResource('currencies', API\CurrencyController::class)->only(['index'])->names('currencies');
    Route::apiResource('transactions', API\TransactionController::class)->only(['index'])->names('transactions');
});
