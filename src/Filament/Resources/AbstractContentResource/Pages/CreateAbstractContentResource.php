<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;

class CreateAbstractContentResource extends CreateRecord
{
    use CanCheckSlug;

    protected static string $resource = AbstractContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->extraAttributes(['style' => 'display:none']),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()->extraAttributes(['style' => 'display:none'])] : []),
            $this->getCancelFormAction()->extraAttributes(['style' => 'display:none']),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $this->generateSlug($data);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = $this->generateSlug($data);

        return $data;
    }
}
