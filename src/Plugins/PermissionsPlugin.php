<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\UserResource;
use Portable\FilaCms\Filament\Resources\RoleResource;
use Portable\FilaCms\Filament\Resources\PermissionResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class PermissionsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-permissions';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PermissionResource::class,
            RoleResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
