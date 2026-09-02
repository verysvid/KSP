<?php

use App\Http\Controllers\MemberDeductionReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'branch', 'can:report.member-deductions.view'])
    ->prefix('reports/member-deductions')
    ->name('reports.member-deductions.')
    ->group(function () {
        Route::get('/', [MemberDeductionReportController::class, 'index'])->name('index');
        Route::get('/excel', [MemberDeductionReportController::class, 'excel'])->name('excel');
        Route::get('/pdf', [MemberDeductionReportController::class, 'pdf'])->name('pdf');
    });
