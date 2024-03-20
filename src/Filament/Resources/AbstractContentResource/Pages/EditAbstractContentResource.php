<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Kenepa\ResourceLock\Resources\Pages\Concerns\UsesResourceLock;
use Mansoor\FilamentVersionable\Page\RevisionsAction;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;

class EditAbstractContentResource extends EditRecord
{
    use UsesResourceLock;

    protected static string $resource = AbstractContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RevisionsAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : null;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
