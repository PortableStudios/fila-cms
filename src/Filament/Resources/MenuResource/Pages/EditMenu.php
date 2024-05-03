<?php

namespace Portable\FilaCms\Filament\Resources\MenuResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\MenuResource;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
