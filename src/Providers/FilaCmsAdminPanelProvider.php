<?php

namespace Portable\FilaCms\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Portable\FilaCms\Filament\Pages\EditSettings;
use Portable\FilaCms\Filament\Pages\MediaLibrary;
use Portable\FilaCms\Filament\Pages\UserSettings;
use Portable\FilaCms\Http\Middleware\FilaCmsAuthenticate;
use Portable\FilaCms\Http\Middleware\TwoFactorMiddleware;

class FilaCmsAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $middleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];

        if (config('fila-cms.auth.force_2fa', true)) {
            $middleware[] = TwoFactorMiddleware::class;
        }


        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->unsavedChangesAlerts()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->plugins($this->getPlugins())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                EditSettings::class,
                UserSettings::class,
                MediaLibrary::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->navigationGroups([
                'Content',
                'Taxonomies',
                'System',
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Settings')
                    ->url('/admin/user-settings')
                    ->icon('heroicon-o-cog-6-tooth')
            ])
            ->databaseNotifications()
            ->middleware($middleware)
            ->darkMode(config('fila-cms.admin_dark_mode', true))
            ->authMiddleware([
                Authenticate::class,
                FilaCmsAuthenticate::class,
            ]);
    }

    protected function getPlugins(): array
    {
        $pluginClasses = config('fila-cms.admin_plugins');
        $plugins = [];
        foreach ($pluginClasses as $pluginClass) {
            $plugins[] = new $pluginClass();
        }

        return $plugins;
    }
}
