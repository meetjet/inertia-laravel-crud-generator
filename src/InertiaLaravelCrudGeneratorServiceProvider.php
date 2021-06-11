<?php

namespace Wpseed\InertiaLaravelCrudGenerator;

use Illuminate\Support\ServiceProvider;
use Wpseed\InertiaLaravelCrudGenerator\Commands\MakeInertiaCrudCommand;
use Wpseed\InertiaLaravelCrudGenerator\Commands\MakeModuleInertiaCrudCommand;

class InertiaLaravelCrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'wpseed');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'wpseed');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/inertia-laravel-crud-generator.php', 'inertia-laravel-crud-generator');

        // Register the service the package provides.
        $this->app->singleton('inertia-laravel-crud-generator', function ($app) {
            return new InertiaLaravelCrudGenerator;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['inertia-laravel-crud-generator'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/inertia-laravel-crud-generator.php' => config_path('inertia-laravel-crud-generator.php'),
        ], 'inertia-laravel-crud-generator.config');

        $this->publishes([
            __DIR__.'/../src/Commands/stubs' => resource_path('stubs/vendor/inertia-laravel-crud-generator'),
        ], 'inertia-laravel-crud-generator.stubs');

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/wpseed'),
        ], 'inertia-laravel-crud-generator.views');*/

        // Registering package commands.
        $this->commands([MakeInertiaCrudCommand::class]);
        $this->commands([MakeModuleInertiaCrudCommand::class]);
    }
}
