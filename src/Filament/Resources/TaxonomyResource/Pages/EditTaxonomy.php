<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Mansoor\FilamentVersionable\Page\RevisionsAction;
use Portable\FilaCms\Filament\Resources\TaxonomyResource;
use Portable\FilaCms\Models\TaxonomyTerm;

class EditTaxonomy extends EditRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RevisionsAction::make(),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    $terms = TaxonomyTerm::where('taxonomy_id', $this->record->id)
                        ->whereHas('taxonomyables')
                        ->first();

                    if (is_null($terms) === false) {
                        Notification::make()
                            ->danger()
                            ->title('Unable to delete Taxonomy')
                            ->body('One or more terms under this taxonomy is currently in use')
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        $record->resources()->delete();

        $rows = [];
        foreach ($data['taxonomy_resources'] as $resource) {
            $rows[] = ['resource_class' => $resource, 'taxonomy_id' => $record->id];
        }

        $record->resources()->insert($rows);

        return $record;
    }
}
