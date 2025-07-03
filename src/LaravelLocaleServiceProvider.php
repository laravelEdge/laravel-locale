<?php

namespace Laraveledge\LaravelLocale;

use Illuminate\Support\ServiceProvider;

class LaravelLocaleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Allow user to publish the config file
        $this->publishes([
            __DIR__ . '/config/locale.php' => $this->app->configPath('locale.php'),
        ], 'laravel-locale-config');
    }

    public function register(): void
    {
        // Merge default config into Laravel's config
        $this->mergeConfigFrom(
            __DIR__ . '/config/locale.php',
            'locale'
        );
    }
}

