<?php

namespace Aghfatehi\Msegat;

use Aghfatehi\Msegat\Commands\CheckBalanceCommand;
use Aghfatehi\Msegat\Commands\ListSendersCommand;
use Aghfatehi\Msegat\Notifications\MsegatChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel service provider for the Msegat SMS package.
 *
 * Registers the MsegatManager singleton, publishes config/migrations,
 * loads webhook routes, and registers the notification channel.
 */
class MsegatServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * Merges package config and registers MsegatManager as a singleton
     * under both the class name and the 'msegat' alias.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/msegat.php', 'msegat');

        $this->app->singleton(MsegatManager::class, function () {
            return new MsegatManager;
        });

        $this->app->singleton('msegat', function () {
            return new MsegatManager;
        });
    }

    /**
     * Bootstrap package services.
     *
     * Publishes config and migrations (console only), loads migrations,
     * registers webhook routes, and extends Laravel's notification system
     * with the 'msegat' channel.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/msegat.php' => config_path('msegat.php'),
            ], 'msegat-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'msegat-migrations');

            $this->commands([
                CheckBalanceCommand::class,
                ListSendersCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerWebhookRoutes();

        Notification::extend('msegat', function () {
            return new MsegatChannel;
        });
    }

    /**
     * Load webhook routes if the route cache is not active.
     */
    private function registerWebhookRoutes(): void
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__.'/Http/routes.php';
        }
    }
}
