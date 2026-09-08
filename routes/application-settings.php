<?php

use App\Http\Controllers\ApplicationSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/application-settings', [ApplicationSettingController::class, 'edit'])
        ->middleware('can:application-setting.view')
        ->name('application-settings.edit');

    Route::put('/application-settings', [ApplicationSettingController::class, 'update'])
        ->middleware('can:application-setting.edit')
        ->name('application-settings.update');
});
