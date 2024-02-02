<?php

namespace Portable\FilaCms\Filament\Resources\PermissionResource\Pages;

use Portable\FilaCms\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

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
