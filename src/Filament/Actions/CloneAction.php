<?php

namespace Portable\FilaCms\Filament\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class CloneAction extends Action
{
    protected $currentFile;

    public static function getDefaultName(): ?string
    {
        return 'clone';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Clone');
        $this->color('secondary');
        $this->icon('heroicon-m-document-duplicate');

        $this->action(function (Model $record): void {
            $result = true;
            $model = get_class($record);
            $data = $record->toArray();
            $data['slug'] = $this->getNewSlug($data['slug']);

            // no need to remove ID and dates
            // because it'll be ignored on mass assignment
            $newRecord = $model::create($data);

            if (! $newRecord) {
                $this->failure();

                return;
            }

            Notification::make()
                ->title('Item cloned successfully')
                ->success()
                ->send();
            $this->success();
        });
    }

    protected function getNewSlug($slug)
    {
        // if other rules will be applied for the slug
        // apply here
        return $slug . '-clone';
    }
}
