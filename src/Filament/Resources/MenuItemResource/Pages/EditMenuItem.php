<?php

namespace Portable\FilaCms\Filament\Resources\MenuItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\MenuItemResource;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
