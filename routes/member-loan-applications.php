<?php

use App\Http\Controllers\MemberLoanApplicationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'branch'])
    ->prefix('member-loan-applications')
    ->name('member-loan-applications.')
    ->group(function () {
        Route::get('/', [MemberLoanApplicationController::class, 'index'])
            ->middleware('can:member-loan-application.view')
            ->name('index');

        Route::get('/create', [MemberLoanApplicationController::class, 'create'])
            ->middleware('can:member-loan-application.create')
            ->name('create');

        Route::post('/', [MemberLoanApplicationController::class, 'store'])
            ->middleware('can:member-loan-application.create')
            ->name('store');

        Route::get('/{loan}', [MemberLoanApplicationController::class, 'show'])
            ->middleware('can:member-loan-application.view')
            ->name('show');

        Route::get('/{loan}/edit', [MemberLoanApplicationController::class, 'edit'])
            ->middleware('can:member-loan-application.edit')
            ->name('edit');

        Route::put('/{loan}', [MemberLoanApplicationController::class, 'update'])
            ->middleware('can:member-loan-application.edit')
            ->name('update');

        Route::delete('/{loan}', [MemberLoanApplicationController::class, 'destroy'])
            ->middleware('can:member-loan-application.delete')
            ->name('destroy');

        Route::get('/{loan}/simulation', [MemberLoanApplicationController::class, 'simulation'])
            ->middleware('can:member-loan-application.view')
            ->name('simulation');

        Route::patch('/{loan}/submit', [MemberLoanApplicationController::class, 'submit'])
            ->middleware('can:member-loan-application.submit')
            ->name('submit');
    });
