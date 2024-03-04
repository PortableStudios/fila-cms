<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Filament\Resources\TaxonomyResource;

class EditTaxonomy extends EditRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        $record->resources()->delete();

        $rows = [];
        foreach ($data['taxonomy_resources'] as $resource) {
            $rows[] = ['resource_class' => $resource, 'taxonomy_id' => $record->id];
        }

        $record->resources()->insert($rows);

        return $record;
    }
}
