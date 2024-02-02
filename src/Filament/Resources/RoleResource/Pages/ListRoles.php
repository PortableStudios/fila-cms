<?php

namespace Portable\FilaCms\Filament\Resources\RoleResource\Pages;

use Portable\FilaCms\Filament\Resources\RoleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
