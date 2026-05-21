<?php

namespace App\Providers;

use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class PaymentGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            return new PaymentGatewayManager($app);
        });
    }

    public function boot(): void
    {
        //
    }
}
