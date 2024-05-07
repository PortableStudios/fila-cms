<?php

namespace Portable\FilaCms\Filament\Resources\FormResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Portable\FilaCms\Filament\Resources\FormResource;
use Portable\FilaCms\Filament\Resources\FormResource\Actions\PreviewAction;

class CreateForm extends CreateRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviewAction::make()
        ];
    }
}
