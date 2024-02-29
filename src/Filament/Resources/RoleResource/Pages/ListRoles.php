<?php

namespace Portable\FilaCms\Filament\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Portable\FilaCms\Filament\Resources\RoleResource;

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
