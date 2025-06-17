<?php

namespace Larangular\RoutingController;

use Illuminate\Support\ServiceProvider;

class RoutingControllerServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Merge package config with application's config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/routing-controller.php',
            'routing-controller'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Allow config to be published to the app's config directory
        $this->publishes([
            __DIR__ . '/../config/routing-controller.php' => config_path('routing-controller.php'),
        ], 'config');
    }
}
