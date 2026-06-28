<?php

namespace RoyalPanel\RoyalAtelier;

use Illuminate\Support\ServiceProvider;
use RoyalPanel\RoyalAtelier\Libraries\ExtensionLibrary;
use RoyalPanel\RoyalAtelier\Console\Commands\RxInstallCommand;
use RoyalPanel\RoyalAtelier\Console\Commands\RxUninstallCommand;
use RoyalPanel\RoyalAtelier\Console\Commands\RxInstallExtensionCommand;
use RoyalPanel\RoyalAtelier\Console\Commands\RxRemoveExtensionCommand;

class AtelierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExtensionLibrary::class, fn() => new ExtensionLibrary());

        $this->mergeConfigFrom(__DIR__ . '/../config/rxframework.php', 'rxframework');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'rxadmin');
        $this->loadRoutesFrom(__DIR__ . '/Routes/rxadmin.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/extensions.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RxInstallCommand::class,
                RxUninstallCommand::class,
                RxInstallExtensionCommand::class,
                RxRemoveExtensionCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../public' => public_path('rx-assets'),
        ], 'rx-assets');

        $this->publishes([
            __DIR__ . '/../config/rxframework.php' => config_path('rxframework.php'),
        ], 'rx-config');
    }
}
