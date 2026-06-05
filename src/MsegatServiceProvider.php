<?php

namespace Aghfatehi\Msegat;

use Aghfatehi\Msegat\Commands\CheckBalanceCommand;
use Aghfatehi\Msegat\Commands\ListSendersCommand;
use Aghfatehi\Msegat\Notifications\MsegatChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class MsegatServiceProvider extends ServiceProvider
{
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

    private function registerWebhookRoutes(): void
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__.'/Http/routes.php';
        }
    }
}
