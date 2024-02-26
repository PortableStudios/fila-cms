<?php

namespace Portable\FilaCms\Filament\Resources\PermissionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Portable\FilaCms\Filament\Resources\PermissionResource;

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