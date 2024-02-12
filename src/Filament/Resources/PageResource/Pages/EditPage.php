<?php

namespace Portable\FilaCms\Filament\Resources\PageResource\Pages;

use Portable\FilaCms\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Models\Scopes\PublishedScope;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
