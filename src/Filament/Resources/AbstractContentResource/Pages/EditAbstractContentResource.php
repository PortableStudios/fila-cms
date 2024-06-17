<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Kenepa\ResourceLock\Resources\Pages\Concerns\UsesResourceLock;
use Mansoor\FilamentVersionable\Page\RevisionsAction;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use Portable\FilaCms\Filament\Actions\RestoreAction;
use Illuminate\Support\Facades\Schema;

class EditAbstractContentResource extends EditRecord
{
    use UsesResourceLock;
    use CanCheckSlug;

    protected static string $resource = AbstractContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->submit(null)
                ->hidden(fn ($record) => $record->trashed())
                ->action('save'),
            RevisionsAction::make()->hidden(fn ($record) => $record->trashed()),
            RestoreAction::make(),
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    if (Schema::hasColumn($record->getTable(), 'is_draft')) {
                        $record->update([
                            'is_draft' => true
                        ]);
                    }
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->extraAttributes(['style' => 'display:none']),
            $this->getCancelFormAction()->extraAttributes(['style' => 'display:none']),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = $this->generateSlug($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getPreviewAction(): Action
    {
        return Action::make('preview')
            ->label('Preview')
            ->color('gray');
    }
}
