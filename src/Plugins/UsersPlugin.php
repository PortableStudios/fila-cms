<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\UserResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class UsersPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-users';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            UserResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
