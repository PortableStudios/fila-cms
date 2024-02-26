<?php

namespace Portable\FilaCms\Filament\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\RoleResource;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}