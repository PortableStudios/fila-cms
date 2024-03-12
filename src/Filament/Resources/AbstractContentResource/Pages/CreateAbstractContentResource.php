<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;

class CreateAbstractContentResource extends CreateRecord
{
    protected static string $resource = AbstractContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : null;

        return $data;
    }
}
