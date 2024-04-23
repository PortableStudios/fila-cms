<?php

namespace Portable\FilaCms\Filament\Resources\NavigationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Portable\FilaCms\Filament\Resources\NavigationResource;
use Filament\Actions;

class ListNavigations extends ListRecords
{
    protected static string $resource = NavigationResource::class;

    public function isTableSearchable(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
