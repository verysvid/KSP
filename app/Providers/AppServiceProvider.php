<?php

namespace App\Providers;

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $settings = Schema::hasTable('application_settings')
                ? ApplicationSetting::current()
                : ApplicationSetting::defaults();
        } catch (Throwable) {
            $settings = ApplicationSetting::defaults();
        }

        // Variabel global yang otomatis tersedia di seluruh Blade:
        // $appSettings->system_name
        // $appSettings->title_1
        // $appSettings->title_2
        // $appSettings->abbreviation
        // $appSettings->description
        // $appSettings->copyright
        // $appSettings->logo_url
        // $appSettings->icon_url
        View::share('appSettings', $settings);
    }
}
