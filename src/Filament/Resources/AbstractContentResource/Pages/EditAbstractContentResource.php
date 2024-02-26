<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use Portable\FilaCms\Models\TaxonomyResource;

class EditAbstractContentResource extends EditRecord
{
    protected static string $resource = AbstractContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $virtualFields = [];
        TaxonomyResource::where('resource_class', static::$resource)->get()->each(function (TaxonomyResource $taxonomyResource) use (&$virtualFields, &$data) {
            $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_').'_ids';
            $virtualFields[$fieldName] = isset($data[$fieldName]) ? $data[$fieldName] : [];
            data_forget($data, $fieldName);
        });

        $record = parent::handleRecordUpdate($record, $data);

        return $record;
        $record->terms()->delete();
        foreach ($virtualFields as $fieldName) {
            $items = isset($data[$fieldName]) ? $data[$fieldName] : [];
            $record->terms()->attach($items);
        }

        return $record;
    }
}
