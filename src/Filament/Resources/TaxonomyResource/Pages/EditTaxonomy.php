<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;

use Portable\FilaCms\Filament\Resources\TaxonomyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxonomy extends EditRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
