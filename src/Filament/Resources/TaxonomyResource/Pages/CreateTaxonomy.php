<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Filament\Resources\TaxonomyResource;

class CreateTaxonomy extends CreateRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        $rows = [];
        foreach ($data['taxonomy_resources'] as $resource) {
            $rows[] = ['resource_class' => $resource, 'taxonomy_id' => $record->id];
        }

        $record->resources()->createMany($rows);

        return $record;
    }
}
