<?php

namespace Portable\FilaCms\Filament\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

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
            $data['slug'] = $this->getNewSlug($model, $data['slug']);
            $data['title'] = '[CLONE] ' . $data['title'];

            // no need to remove ID and dates
            // because it'll be ignored on mass assignment
            $newRecord = $model::create($data);

            // clone roles
            $roles = $record->roles()->pluck('role_id');
            $newRecord->roles()->sync($roles);

            // clone SEO
            $seo = $record->Seo->toArray();
            unset($seo['id']);
            unset($seo['created_at']);
            unset($seo['updated_at']);
            unset($seo['model_id']);
            $newRecord->seo()->update($seo);

            // check for cloneables
            if ($newRecord->cloneables != null) {
                $cloneables = $newRecord->cloneables;

                foreach ($cloneables as $modelName => $fields) {
                    $newData = $record->{$modelName};

                    // then 1-to-1 relationship
                    if ($newData instanceof Model) {
                        $newRecord->{$modelName}()->create($this->pickData($newData, $fields));
                    }

                    // then 1 to many
                    if ($newData instanceof Collection) {
                        foreach ($newData as $key => $newRow) {
                            $newRecord->{$modelName}()->create($this->pickData($newRow, $fields));
                        }
                    }

                }
            }

            if (! $newRecord) {
                $this->failure();

                return;
            }

            Notification::make()
                ->title('Item cloned successfully')
                ->success()
                ->send();

            $this->success();

            \Log::info(get_class($newRecord));
            $resource = $newRecord::getResourceName();
            \Log::info($resource);
            redirect($resource::getUrl('edit', ['record' => $newRecord]));
        });
    }

    protected function pickData(Model $data, $fields)
    {
        $newData = [];

        foreach ($fields as $key => $field) {
            $newData[$field] = $data->$field;
        }

        return $newData;
    }

    protected function getNewSlug($model, $slug)
    {
        $newSlug = $slug;

        // if there is a -clone already, then append 1 or increment
        $result = $model::withoutGlobalScopes()->where('slug', $newSlug)->first();

        $count = 1;
        while ($result != null) {
            $incrementedSlug = $newSlug . '-' . $count;

            $result = $model::withoutGlobalScopes()->where('slug', $incrementedSlug)->first();
            $count++;

            if ($result == null) {
                $newSlug = $incrementedSlug;
                break;
            }
        }

        return $newSlug;
    }
}
