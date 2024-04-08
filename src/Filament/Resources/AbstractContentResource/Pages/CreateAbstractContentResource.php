<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use FilaCms;

class CreateAbstractContentResource extends CreateRecord
{
    protected static string $resource = AbstractContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $class = parent::static;
        $parent = new $class();

        FilaCms::getModelFromResource($parent->resource);
        if (is_null($data['slug'])) {
            // auto-generate then check
            $data['slug'] = Str::slug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        return $data;
    }

    protected function checkIfSlugExists($slug)
    {

    }
}
