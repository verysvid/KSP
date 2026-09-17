<?php

use App\Http\Controllers\ShuController;
use App\Http\Controllers\ShuMemberReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'branch'])->group(function () {
    Route::get('/shu', [ShuController::class, 'index'])
        ->middleware('can:shu.view')->name('shu.index');
    Route::get('/shu/create', [ShuController::class, 'create'])
        ->middleware('can:shu.create')->name('shu.create');
    Route::post('/shu', [ShuController::class, 'store'])
        ->middleware('can:shu.create')->name('shu.store');
    Route::get('/shu/{shu}', [ShuController::class, 'show'])
        ->middleware('can:shu.view')->name('shu.show');
    Route::get('/shu/{shu}/edit', [ShuController::class, 'edit'])
        ->middleware('can:shu.create')->name('shu.edit');
    Route::put('/shu/{shu}', [ShuController::class, 'update'])
        ->middleware('can:shu.create')->name('shu.update');
    Route::post('/shu/{shu}/calculate', [ShuController::class, 'calculate'])
        ->middleware('can:shu.calculate')->name('shu.calculate');
    Route::post('/shu/{shu}/finalize', [ShuController::class, 'finalize'])
        ->middleware('can:shu.finalize')->name('shu.finalize');
    Route::post('/shu/{shu}/paid', [ShuController::class, 'markPaid'])
        ->middleware('can:shu.pay')->name('shu.paid');
});

Route::middleware(['auth', 'can:shu-member-report.view'])
    ->get('/reports/shu', [ShuMemberReportController::class, 'index'])
    ->name('reports.shu.index');
