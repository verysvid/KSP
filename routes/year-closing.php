<?php

use App\Http\Controllers\YearClosingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'branch'])->group(function () {
    Route::get('/year-closing', [YearClosingController::class, 'index'])
        ->middleware('can:closing.view')
        ->name('year-closing.index');

    Route::get('/year-closing/preview', [YearClosingController::class, 'preview'])
        ->middleware('can:closing.view')
        ->name('year-closing.preview');

    Route::post('/year-closing/close', [YearClosingController::class, 'close'])
        ->middleware('can:closing.process')
        ->name('year-closing.close');

    Route::get('/year-closing/{yearClosing}', [YearClosingController::class, 'show'])
        ->middleware('can:closing.view')
        ->name('year-closing.show');

    Route::post('/year-closing/{yearClosing}/reopen', [YearClosingController::class, 'reopen'])
        ->middleware('can:closing.process')
        ->name('year-closing.reopen');
});
