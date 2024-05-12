<?php

namespace Portable\FilaCms\Filament\Resources\FormResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\FormResource;
use Portable\FilaCms\Filament\Resources\FormResource\Actions\PreviewAction;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            PreviewAction::make()
        ];
    }
}
