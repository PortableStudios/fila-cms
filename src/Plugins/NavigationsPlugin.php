<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\NavigationResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class NavigationsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-navigation';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            NavigationResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
