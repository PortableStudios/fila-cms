<?php

namespace Portable\FilaCms\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Portable\FilaCms\Filament\Resources\PermissionResource;
use Portable\FilaCms\Filament\Resources\RoleResource;

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
            RoleResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
