<?php

namespace Portable\FilaCms\Filament\Resources\PageResource\Pages;

use Filament\Actions;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages\EditAbstractContentResource;
use Portable\FilaCms\Filament\Resources\PageResource;

class EditPage extends EditAbstractContentResource
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
