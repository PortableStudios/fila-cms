<?php

namespace Portable\FilaCms\Plugins;

use Portable\FilaCms\Filament\Resources\TaxonomyResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class TaxonomyPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-taxonomies';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            TaxonomyResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
