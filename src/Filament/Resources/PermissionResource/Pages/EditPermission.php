<?php

namespace Portable\FilaCms\Filament\Resources\PermissionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\PermissionResource;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}