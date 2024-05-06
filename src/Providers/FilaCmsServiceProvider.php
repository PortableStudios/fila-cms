<?php

namespace Portable\FilaCms\Providers;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Portable\FilaCms\Actions\Fortify\ResetUserPassword;
use Portable\FilaCms\Data\DummyForm;
use Portable\FilaCms\Facades\FilaCms as FacadesFilaCms;
use Portable\FilaCms\FilaCms;
use Portable\FilaCms\Filament\Blocks\RelatedResourceBlock;
use Portable\FilaCms\Filament\Forms\Components\AddressInput;
use Portable\FilaCms\Filament\Forms\Components\ImagePicker;
use Portable\FilaCms\Listeners\AuthenticationListener;
use Portable\FilaCms\Models\Setting;
use Portable\FilaCms\Services\MediaLibrary;

class FilaCmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadSettings();
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
                if(file_exists(realpath(resource_path('../../../../../resources/css/filacms.css')))) {
                    return app('Illuminate\Foundation\Vite')('../../../../resources/css/filacms.css');
                }

                // Check if there's a local FilaCMS.css
                if (file_exists(resource_path('css/filacms.css'))) {
                    return app('Illuminate\Foundation\Vite')('resources/css/filacms.css');
                }

                return app('Illuminate\Foundation\Vite')('vendor/portable/filacms/resources/css/filacms.css');
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

        $this->registerSettingsFields();

    }

    protected function loadSettings()
    {
        if(!Schema::hasTable('settings')) {
            return;
        }

        $form = new DummyForm();
        $container = new ComponentContainer($form);

        $fields = FacadesFilaCms::getSettingsFields();
        $fields = collect($fields)->flatten();
        $container->schema($fields->toArray());

        $values = [];
        foreach($fields as $field) {
            if(!method_exists($field, 'getName')) {
                continue;
            }
            $values[$field->getName()] = Setting::get($field->getName());
        }

        $container->fill($values);

        $data = (array)$form;

        foreach(array_keys($values) as $fieldName) {
            config(['settings.'.$fieldName => data_get($data, $fieldName)]);
        }


        return true;

    }

    protected function registerSettingsFields()
    {
        FacadesFilaCms::registerSetting('SEO & Analytics', 'Organisation Details', 0, function () {
            return [
                TextInput::make('seo.organisation.name')->label('Organisation Name')->columnSpanFull(),
                TextInput::make('seo.organisation.email')->label('Organisation Email'),
                TextInput::make('seo.organisation.phone')->label('Organisation Phone'),
                AddressInput::make('seo.organisation.address')->label('Organisation Address')->required(true)
                ->mutateDehydratedStateUsing(function ($state) {
                    return json_encode($state);
                })->afterStateHydrated(function (AddressInput $component, $state) {
                    $component->state(json_decode($state, true));
                })->columnSpanFull(),
                TextInput::make('seo.organisation.facebook')->label('Facebook Url')->url(),
                TextInput::make('seo.organisation.linkedIn')->label('LinkedIn Url')->url(),
                TextInput::make('seo.organisation.instagram')->label('Instagram Url')->url(),
                TextInput::make('seo.organisation.twitter')->label('Twitter Url')->url(),
            ];
        });

        FacadesFilaCms::registerSetting('SEO & Analytics', 'Global SEO', 1, function () {
            return [
                TextInput::make('seo.global.site_name')->label('Site Name'),
                ImagePicker::make('seo.global.image')->label('SEO Image'),
            ];
        });

        FacadesFilaCms::registerSetting('SEO & Analytics', 'Tracking', 1, function () {
            return [
                Textarea::make('seo.tracking.gtm_code')->label('GTM Code')->columnSpanFull(),
            ];
        });

    }
}
