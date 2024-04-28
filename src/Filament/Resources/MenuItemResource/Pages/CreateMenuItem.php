<?php

namespace Portable\FilaCms\Filament\Resources\MenuItemResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Portable\FilaCms\Filament\Resources\MenuItemResource;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $jsonData = [];

        if ($data['type'] === 'url') {
            $jsonData['url'] = $data['reference_text'];
        }

        if ($data['type'] === 'page') {
            $resourceClass = $data['reference_page'];
            $prefix = method_exists($resourceClass, 'getFrontendRoutePrefix') ? $resourceClass::getFrontendRoutePrefix() : $resourceClass::getRoutePrefix();
            $jsonData = [
                'source'    => route($prefix . '.index'),
                'resource'  => $resourceClass,
            ];
        }

        if ($data['type'] === 'content') {
            $resourceClass = $data['reference_page'];
            $jsonData = [
                'model'     => FilaCms::getModelFromResource($data['reference_page']),
                'resource'  => $resourceClass,
                'id'        => $data['reference_content']
            ];
        }

        $data['reference'] = $jsonData;

        return $data;
    }
}
