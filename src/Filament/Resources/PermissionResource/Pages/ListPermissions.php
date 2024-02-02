<?php

namespace Portable\FilaCms\Filament\Resources\PermissionResource\Pages;

use Portable\FilaCms\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
