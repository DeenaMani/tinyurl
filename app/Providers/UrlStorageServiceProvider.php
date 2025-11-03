<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UrlStorageManager;

class UrlStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UrlStorageManager::class, function ($app) {
            return new UrlStorageManager();
        });

        // Merge the tinyurl config
        $this->mergeConfigFrom(__DIR__ . '/../../config/tinyurl.php', 'tinyurl');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish the config file
        $this->publishes([
            __DIR__ . '/../../config/tinyurl.php' => config_path('tinyurl.php'),
        ], 'tinyurl-config');

        // Register the storage mode in app config
        config(['app.storage_mode' => config('tinyurl.storage_mode', env('STORAGE_MODE', 'single'))]);
    }
}
