<?php

namespace Portable\FilaCms\Providers;

use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Portable\FilaCms\Actions\Fortify\ResetUserPassword;
use Portable\FilaCms\Facades\FilaCms as FacadesFilaCms;
use Portable\FilaCms\FilaCms;
use Portable\FilaCms\Filament\Blocks\RelatedResourceBlock;
use Portable\FilaCms\Listeners\AuthenticationListener;
use Portable\FilaCms\Services\MediaLibrary;

class FilaCmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Portable\FilaCms\Commands\InstallCommand::class,
                \Portable\FilaCms\Commands\AddUserConcerns::class,
                \Portable\FilaCms\Commands\MakeUser::class,
                \Portable\FilaCms\Commands\MakeContentResource::class,
                \Portable\FilaCms\Commands\MakeContentMigration::class,
                \Portable\FilaCms\Commands\MakeContentModel::class,
                \Portable\FilaCms\Commands\MakeContentPermissionSeeder::class,
            ]);
        }
        $this->loadRoutesFrom(__DIR__ . '/../../routes/filacms-routes.php');

        if (config('fila-cms.publish_content_routes')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/frontend-routes.php');
        }
        //$this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../../views', 'fila-cms');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        \Filament\Support\Facades\FilamentIcon::register([
            'filament-password-input::regenerate' => 'heroicon-m-key',
        ]);

        Livewire::component('portable.fila-cms.livewire.content-resource-list', \Portable\FilaCms\Livewire\ContentResourceList::class);
        Livewire::component('portable.fila-cms.livewire.content-resource-show', \Portable\FilaCms\Livewire\ContentResourceShow::class);
        Livewire::component('media-library-table', \Portable\FilaCms\Livewire\MediaLibraryTable::class);
        Blade::componentNamespace('Portable\\FilaCms\\Views\\Components', 'fila-cms');
        config(['versionable.user_model' => config('auth.providers.users.model')]);

        Event::listen(Login::class, AuthenticationListener::class);

        Fortify::resetPasswordView(function () {
            return view('fila-cms::auth.reset-password');
        });

        Fortify::loginView(function () {
            return redirect(route('filament.admin.auth.login'));
        });

        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }

    public function register()
    {
        $this->app->bind('FilaCms', FilaCms::class);
        $this->app->bind('MediaLibrary', \Portable\FilaCms\Services\MediaLibrary::class);

        $loader = AliasLoader::getInstance();
        $loader->alias('FilaCms', FacadesFilaCms::class);
        $loader->alias('MediaLibrary', \Portable\FilaCms\Facades\MediaLibrary::class);

        $this->app->bind('fila-cms', function () {
            return new FilaCms();
        });

        $this->app->bind('fila-cms-media', function () {
            return new MediaLibrary();
        });

        $this->publishes([
            __DIR__ . '/../../config/fila-cms.php' => config_path('fila-cms.php'),
        ], 'fila-cms-config');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/fila-cms.php',
            'fila-cms'
        );

        if (config('fila-cms.use_admin_panel')) {
            $this->app->register(FilaCmsAdminPanelProvider::class);
        }

        Blade::directive('filaCmsStyles', function (string $expression): string {
            try {
                // Check if there's a local FilaCMS.css
                if (file_exists(resource_path('css/filacms.css'))) {
                    return app('Illuminate\Foundation\Vite')('resources/css/filacms.css');
                } else {
                    return app('Illuminate\Foundation\Vite')('vendor/portable/filacms/resources/css/filacms.css');
                }
            } catch (\Exception $e) {
                return '';
            }
        });

        TiptapEditor::configureUsing(function (TiptapEditor $component) {
            $component
                ->mediaAction(config('fila-cms.editor.media_action'))
                ->blocks([
                    RelatedResourceBlock::class,
                ]);
        });
    }
}
