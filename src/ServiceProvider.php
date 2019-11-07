<?php

namespace Theomessin\Argo;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Theomessin\Argo\Argo;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('argo.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'argo');

        // Register the main class to use with the facade
        $a = Argo::class;

        $this->app->singleton($a, function () {
            return new Argo();
        });
        $this->app->alias($a, 'argo');
    }
}
