<?php

namespace Portable\FilaCms\Filament\Resources\NavigationResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\NavigationResource;
use Filament\Actions;
use Portable\FilaCms\Facades\FilaCms;

class EditNavigation extends EditRecord
{
    protected static string $resource = NavigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($data['type'] === 'url') {
            $data['reference_text'] = $data['reference']['url'];
        }

        if ($data['type'] === 'page') {
            $data['reference_page'] = $data['reference']['resource'];
        }
        if ($data['type'] === 'content') {
            $data['reference_page'] = $data['reference']['resource'];
            $data['reference_content'] = $data['reference']['id'];
        }

        return $data;
    }
}
