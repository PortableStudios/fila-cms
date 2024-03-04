<?php

namespace Portable\FilaCms\Filament\Resources\PageResource\Pages;

use Filament\Actions;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages\ListAbstractContentResources;
use Portable\FilaCms\Filament\Resources\PageResource;

class ListPages extends ListAbstractContentResources
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
