<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DeviceControl\DeviceControlManager;
use App\Services\DeviceControl\DeviceControlService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeviceControlManager::class, function ($app) {
            return new DeviceControlManager();
        });

        $this->app->singleton(DeviceControlService::class, function ($app) {
            return new DeviceControlService($app->make(DeviceControlManager::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
