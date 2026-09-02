<?php

use App\Http\Controllers\MemberLoanReportController;
use Illuminate\Support\Facades\Route;

Route::middleware([
	'auth',
	'can:member-loan-report.view',
	])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get(
            '/loans',
            [MemberLoanReportController::class, 'index']
        )->name('loans.index');

        Route::get(
            '/loans/{loan}',
            [MemberLoanReportController::class, 'show']
        )->name('loans.show');
    });
