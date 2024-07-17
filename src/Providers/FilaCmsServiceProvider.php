<?php

namespace Portable\FilaCms\Providers;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Portable\FilaCms\Actions\Fortify\UpdateUserProfileInformation;
use Portable\FilaCms\Data\DummyForm;
use Portable\FilaCms\Facades\FilaCms as FacadesFilaCms;
use Portable\FilaCms\FilaCms;
use Portable\FilaCms\Filament\Blocks\AccordionBlock;
use Portable\FilaCms\Filament\Blocks\RelatedResourceBlock;
use Portable\FilaCms\Filament\Forms\Components\AddressInput;
use Portable\FilaCms\Filament\Forms\Components\ImagePicker;
use Portable\FilaCms\Fortify\Http\Responses\TwoFactorConfirmedResponse;
use Portable\FilaCms\Listeners\AuthenticationListener;
use Portable\FilaCms\Listeners\UserVerifiedListener;
use Portable\FilaCms\Models\Setting;
use Portable\FilaCms\Observers\AuthenticatableObserver;
use Portable\FilaCms\Services\MediaLibrary;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class FilaCmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function ($app) {
            $schedule = $app->make('Illuminate\Console\Scheduling\Schedule');
            $schedule->command('fila-cms:generate-sitemap')->daily();
            $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])->daily();
            $schedule->command('schedule-monitor:sync')->daily();
        });


        $this->bootLinkedInSocialite();
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
                \Portable\FilaCms\Commands\MakeContents::class,
                \Portable\FilaCms\Commands\SyncSearch::class,
                \Portable\FilaCms\Commands\GenerateSitemap::class
            ]);
        }
        $this->loadRoutesFrom(__DIR__ . '/../../routes/filacms-routes.php');

        if (config('fila-cms.publish_content_routes')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/frontend-routes.php');
        }

        $this->loadViewsFrom(
            [
                resource_path('views/fila-cms'),
                __DIR__ . '/../../resources/views'
            ],
            'fila-cms'
        );
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        \Filament\Support\Facades\FilamentIcon::register([
            'filament-password-input::regenerate' => 'heroicon-m-key',
        ]);

        Livewire::component('portable.fila-cms.livewire.content-resource-list', \Portable\FilaCms\Livewire\ContentResourceList::class);
        Livewire::component('portable.fila-cms.livewire.content-resource-show', \Portable\FilaCms\Livewire\ContentResourceShow::class);
        Livewire::component('media-library-table', \Portable\FilaCms\Livewire\MediaLibraryTable::class);
        Livewire::component('form-show', \Portable\FilaCms\Livewire\FormShow::class);
        Blade::componentNamespace('Portable\\FilaCms\\Views\\Components', 'fila-cms');
        config(['versionable.user_model' => config('auth.providers.users.model')]);
        config(['scout.driver' => config('fila-cms.search.driver', 'meilisearch')]);

        Event::listen(Login::class, AuthenticationListener::class);
        Event::listen(Verified::class, UserVerifiedListener::class);

        Fortify::resetPasswordView(function () {
            return view(config('fila-cms.auth.password_reset_view'));
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view(config('fila-cms.auth.forgot_password_view'));
        });

        Fortify::loginView(function () {
            return view('fila-cms::auth.login');
        });

        Fortify::confirmPasswordView(function () {
            return view('fila-cms::auth.confirm-password');
        });

        // Register our exception handler
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Portable\FilaCms\Exceptions\Handler::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \Portable\FilaCms\Http\Responses\LoginResponse::class
        );

        Fortify::verifyEmailView('fila-cms::auth.verify-email');
        Fortify::resetUserPasswordsUsing(config('fila-cms.auth.password_reset'));
        Fortify::updateUserProfileInformationUsing(config('fila-cms.users.profile_updater', UpdateUserProfileInformation::class));

        config('auth.providers.users.model')::observe(AuthenticatableObserver::class);
        Fortify::twoFactorChallengeView(function () {
            return view('fila-cms::auth.two-factor-challenge');
        });

        $this->app->singleton(TwoFactorConfirmedResponseContract::class, TwoFactorConfirmedResponse::class);
    }

    public function register()
    {
        try {
            $assets[] = Js::make('tiptap-custom-extension-scripts', Vite::asset('resources/js/tiptap/extensions.js'))->module(true);
            FilamentAsset::register($assets, 'awcodes/tiptap-editor');
        } catch(\Exception $e) {
            // Do nothing, assets may not yet be built
        }

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
                if (file_exists(realpath(resource_path('../../../../../resources/css/filacms.css')))) {
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
                ->linkAction(config('fila-cms.editor.link_action', \Portable\FilaCms\Filament\Actions\LinkAction::class))
                ->mediaAction(config('fila-cms.editor.media_action'))
                ->blocks([
                    RelatedResourceBlock::class,
                    AccordionBlock::class,
                    ...config('fila-cms.tip_tap_blocks')
                ]);
        });

        $this->registerSettingsFields();
    }

    protected function loadSettings()
    {
        try {
            if (!Schema::hasTable('settings')) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $form = new DummyForm();
        $container = new ComponentContainer($form);

        $fields = FacadesFilaCms::getSettingsFields();
        $fields = collect($fields)->flatten();
        $container->schema($fields->toArray());

        $values = [];
        foreach ($fields as $field) {
            if (!method_exists($field, 'getName')) {
                continue;
            }
            $values[$field->getName()] = Setting::get($field->getName());
        }

        $container->fill($values);

        $data = (array)$form;

        foreach (array_keys($values) as $fieldName) {
            config(['settings.' . $fieldName => data_get($data, $fieldName)]);
        }

        $providers = config('fila-cms.sso.providers', ['google','facebook','linkedin']);
        foreach ($providers as $provider) {
            if (config('settings.sso.' . $provider . '.client_id') && config('settings.sso.' . $provider . '.client_secret')) {
                config(['services.' . $provider . '.client_id' => config('settings.sso.' . $provider . '.client_id')]);
                config(['services.' . $provider . '.client_secret' => config('settings.sso.' . $provider . '.client_secret')]);
                config(['services.' . $provider . '.redirect' => '/login/' . $provider . '/callback']);
            }
        }

        if('settings.monitoring.sentry.dsn') {
            config(['sentry.dsn' => config('settings.monitoring.sentry.dsn')]);
        }

        if('monitoring.ohdear.enabled') {
            config(['schedule-monitor.api_token' => config('settings.monitoring.ohdear.api_token')]);
            config(['schedule-monitor.site_id' => config('settings.monitoring.ohdear.site_id')]);
            config(['schedule-monitor.queue' => env('OH_DEAR_QUEUE', 'default')]);
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
                AddressInput::make('seo.organisation.address')->label('Organisation Address')
                    ->mutateDehydratedStateUsing(function ($state) {
                        return json_encode($state);
                    })
                    ->afterStateHydrated(function (AddressInput $component, $state) {
                        if (is_string($state)) {
                            $component->state(json_decode($state, true));
                        }
                    })
                    ->columnSpanFull(),
                TextInput::make('seo.organisation.facebook')->label('Facebook Url')->url(),
                TextInput::make('seo.organisation.linkedIn')->label('LinkedIn Url')->url(),
                TextInput::make('seo.organisation.instagram')->label('Instagram Url')->url(),
                TextInput::make('seo.organisation.twitter')->label('Twitter Url')->url(),
            ];
        });

        FacadesFilaCms::registerSetting('SEO & Analytics', 'Global SEO', 1, function () {
            return [
                TextInput::make('seo.global.site_name')->label('Site Name'),
                Textarea::make('seo.global.description')->label('Site Description'),
                ImagePicker::make('seo.global.image')->label('SEO Image'),
            ];
        });

        FacadesFilaCms::registerSetting('SEO & Analytics', 'Tracking', 1, function () {
            return [
                Textarea::make('seo.tracking.gtm_code')->label('GTM Code')->columnSpanFull(),
            ];
        });

        FacadesFilaCms::registerSetting('Single Sign-On', 'Facebook', 1, function () {
            return [
                TextInput::make('sso.facebook.client_id')->label('Client Id'),
                TextInput::make('sso.facebook.client_secret')->label('Client Secret'),
                Placeholder::make('sso.facebook.redirect')
                    ->label('Redirect Url')
                    ->content(url('login/facebook/callback'))
                    ->helperText('Use this as the redirect url in your Facebook app settings'),
            ];
        });

        FacadesFilaCms::registerSetting('Single Sign-On', 'Google', 1, function () {
            return [
                TextInput::make('sso.google.client_id')->label('Client Id'),
                TextInput::make('sso.google.client_secret')->label('Client Secret'),
                Placeholder::make('sso.google.redirect')
                    ->label('Redirect Url')
                    ->content(url('login/google/callback'))
                    ->helperText('Use this as the redirect url in your Google app settings'),
            ];
        });

        FacadesFilaCms::registerSetting('Single Sign-On', 'LinkedIn', 1, function () {
            return [
                TextInput::make('sso.linkedin.client_id')->label('Client Id'),
                TextInput::make('sso.linkedin.client_secret')->label('Client Secret'),
                Placeholder::make('sso.linkedin.redirect')
                    ->label('Redirect Url')
                    ->content(url('login/linkedin/callback'))
                    ->helperText('Use this as the redirect url in your LinkedIn app settings'),
                Placeholder::make('added_product')
                    ->label('App Requirement')
                    ->content(new HtmlString('<strong>OpenID Connect</strong>'))
                    ->helperText('Within the Products section, locate and enable "Sign In with LinkedIn using OpenID Connect"'),
            ];
        });

        FacadesFilaCms::registerSetting("Monitoring", "Sentry", 1, function () {
            return [
                Toggle::make('monitoring.sentry.enabled')->label('Enable Sentry')->columnSpanFull()->live(),
                TextInput::make('monitoring.sentry.dsn')->label('Sentry DSN')->disabled(function (Get $get) {
                    return $get('monitoring.sentry.enabled') !== true;
                })->live(),
            ];
        });

        FacadesFilaCms::registerSetting("Monitoring", "Oh Dear", 1, function () {
            return [
                Toggle::make('monitoring.ohdear.enabled')->label('Enable Oh Dear')->columnSpanFull()->live(),
                TextInput::make('monitoring.ohdear.api_token')->label('API Token')->disabled(function (Get $get) {
                    return $get('monitoring.ohdear.enabled') !== true;
                })
                ->helperText(new HtmlString(
                    'You can generate an API token at the Oh Dear ' .
                    '<a href="https://ohdear.app/user/api-tokens">user settings screen</a>"'
                ))
                ->live(),
                TextInput::make('monitoring.ohdear.site_id')->label('Site ID')->disabled(function (Get $get) {
                    return $get('monitoring.ohdear.enabled') !== true;
                })
                ->helperText("You'll find this id on the settings page of a site at Oh Dear.")
                ->live()
            ];
        });
    }

    protected function bootLinkedInSocialite()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'linkedin',
            function ($app) use ($socialite) {
                $config = config('services.linkedin');
                return $socialite->buildProvider(
                    \Portable\FilaCms\Socialite\FilaCmsLinkedInProvider::class,
                    $config
                );
            }
        );
    }
}
