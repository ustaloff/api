<?php

namespace App\Providers;

use App\Services\BackupManager;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BackupManager::class, function ($app) {
            return new BackupManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}