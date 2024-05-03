<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\MenuResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class MenusPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-menus';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            MenuResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
