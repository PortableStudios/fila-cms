<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\PageResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class PagesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-page';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PageResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
