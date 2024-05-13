<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Filament\Actions\StaticAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Concerns\HasExtraItemActions as ConcernsHasExtraItemActions;
use Filament\Forms\Components\Contracts\HasExtraItemActions;
use Filament\Forms\Components\Field;
use Portable\FilaCms\Models\Media;

class MediaPicker extends Field implements HasExtraItemActions
{
    use ConcernsHasExtraItemActions;

    protected string $view = 'fila-cms::filament.forms.components.media-picker';
    protected ?bool $isLive = true;
    protected ?bool $onlyImage = false;

    public function onlyImage(): static
    {
        $this->onlyImage = true;

        return $this;
    }

    protected function setUp(): void
    {
        $this->registerActions([
            fn (MediaPicker $component): Action => $component->pickMediaAction(),
            fn (MediaPicker $component): Action => $component->clearMediaAction(),
        ]);
    }


    public function hasMedia()
    {
        $model = $this->getState();
        return (bool)$model;
    }

    public function currentMedia()
    {
        $model = $this->getState();
        if ($model) {
            $model = Media::find($model);
        }
        return $model ? $model->small_thumbnail : Media::uploadImage();
    }

    public function clearMediaAction()
    {
        return Action::make('clear-media')
            ->action(function () {
                $this->state(null);
            });
    }

    public function pickImageAction()
    {
        return Action::make('pick-media')
            ->form(
                [
                    MediaTable::make('selected-media')->afterStateUpdated(function ($state) {
                        $livewire = $this->getLivewire();
                        data_set($livewire, $this->getStatePath() . '_current_selection', $state);
                    })
                ]
            )
            ->modalSubmitAction(function (StaticAction $action) {
                return $action->disabled(function () {
                    $livewire = $this->getLivewire();
                    $currentFile = data_get($livewire, $this->getStatePath() . '_current_selection');

                    if(!$currentFile) {
                        return true;
                    }

                    $model = Media::find($currentFile);
                    return (!$model || ($this->onlyImage && !$model->is_image));
                });
            })->action(function () {
                $livewire = $this->getLivewire();
                $currentFile = data_get($livewire, $this->getStatePath() . '_current_selection');
                $this->state($currentFile);
            });
        ;
    }
}
