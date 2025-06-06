<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->server('HTTP_X_FORWARDED_PROTO') == 'https' ||
            request()->server('HTTPS') == 'on' ||
            env('FORCE_HTTPS', false)) {
            \URL::forceScheme('https');
        }

        //
    }
}
