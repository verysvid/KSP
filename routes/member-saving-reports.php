<?php

use App\Http\Controllers\MemberSavingReportController;
use Illuminate\Support\Facades\Route;

Route::middleware([
	'auth',
	'can:member-saving-report.view',
	])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get(
            '/savings',
            [MemberSavingReportController::class, 'index']
        )->name('savings.index');
    });
