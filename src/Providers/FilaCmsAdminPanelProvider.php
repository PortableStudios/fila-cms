<?php

namespace Portable\FilaCms\Providers;

use Portable\FilaCms\Http\Middleware\FilaCmsAuthenticate;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Filament\Widgets;
use Filament\Support\Colors\Color;
use Filament\PanelProvider;
use Filament\Panel;
use Filament\Pages;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\Authenticate;

class FilaCmsAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->plugins($this->getPlugins())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
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
