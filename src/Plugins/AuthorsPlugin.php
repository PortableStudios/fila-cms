<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\AuthorResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class AuthorsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-author';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            AuthorResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
