<?php

namespace Portable\FilaCms\Providers;

use Illuminate\Support\ServiceProvider;

class FilaCmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Portable\FilaCms\Commands\InstallCommand::class,
                \Portable\FilaCms\Commands\AddUserConcerns::class,
                \Portable\FilaCms\Commands\MakeUser::class,
            ]);
        }

        //$this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        //$this->loadViewsFrom(__DIR__.'/../Views', 'fila-cms');
    }

    public function register()
    {
        $this->publishes([
            __DIR__.'/../../config/fila-cms.php' => config_path('fila-cms.php'),
        ], 'fila-cms-config');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/fila-cms.php',
            'fila-cms'
        );

        $this->loadViewsFrom(__DIR__.'/../../views', 'fila-cms');

        if (config('fila-cms.use_admin_panel')) {
            $this->app->register(FilaCmsAdminPanelProvider::class);
        }
    }
}
