<?php

namespace Portable\FilaCms\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Portable\FilaCms\Facades\FilaCms as FacadesFilaCms;
use Portable\FilaCms\FilaCms;

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
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function register()
    {
        $this->app->bind('FilaCms', FilaCms::class);
        $loader = AliasLoader::getInstance();
        $loader->alias('FilaCms', FacadesFilaCms::class);

        $this->app->bind('fila-cms', function () {
            return new FilaCms();
        });

        $this->publishes([
            __DIR__.'/../../config/fila-cms.php' => config_path('fila-cms.php'),
        ], 'fila-cms-config');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__.'/../../config/fila-cms.php',
            'fila-cms'
        );

        $this->loadViewsFrom(__DIR__.'/../../views', 'fila-cms');

        if (config('fila-cms.use_admin_panel')) {
            $this->app->register(FilaCmsAdminPanelProvider::class);
        }
    }
}
