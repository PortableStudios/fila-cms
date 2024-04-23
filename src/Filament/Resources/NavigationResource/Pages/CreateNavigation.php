<?php

namespace Portable\FilaCms\Filament\Resources\NavigationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Portable\FilaCms\Filament\Resources\NavigationResource;
use Portable\FilaCms\Facades\FilaCms;

class CreateNavigation extends CreateRecord
{
    protected static string $resource = NavigationResource::class;

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
