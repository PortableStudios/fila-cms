<?php

namespace Portable\FilaCms\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Portable\FilaCms\Filament\Resources\FormResource;

class FormsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-forms';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            FormResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
