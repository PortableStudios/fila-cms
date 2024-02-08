<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;

use Portable\FilaCms\Filament\Resources\TaxonomyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxonomies extends ListRecords
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
