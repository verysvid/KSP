<?php

use App\Http\Controllers\BulkTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'branch'])->group(function () {
    Route::get('bulk-transactions', [BulkTransactionController::class, 'index'])
        ->middleware('can:bulk-transaction.view')
        ->name('bulk-transactions.index');

    Route::post('bulk-transactions', [BulkTransactionController::class, 'store'])
        ->middleware('can:bulk-transaction.process')
        ->name('bulk-transactions.store');

    Route::get('bulk-transactions/{bulkTransaction}', [BulkTransactionController::class, 'show'])
        ->middleware('can:bulk-transaction.view')
        ->name('bulk-transactions.show');
});
