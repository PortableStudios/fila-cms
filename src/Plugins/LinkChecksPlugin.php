<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\LinkCheckResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class LinkChecksPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-links';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            LinkCheckResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
