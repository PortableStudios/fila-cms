<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Filament\Actions\StaticAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Concerns\HasExtraItemActions as ConcernsHasExtraItemActions;
use Filament\Forms\Components\Contracts\HasExtraItemActions;
use Filament\Forms\Components\Field;
use Portable\FilaCms\Models\Media;

class ImagePicker extends Field implements HasExtraItemActions
{
    use ConcernsHasExtraItemActions;

    protected string $view = 'fila-cms::filament.forms.components.image-picker';
    protected ?bool $isLive = true;

    protected function setUp(): void
    {
        $this->registerActions([
            fn (ImagePicker $component): Action => $component->pickImageAction(),
            fn (ImagePicker $component): Action => $component->clearImageAction(),
        ]);
    }


    public function hasImage()
    {
        $model = $this->getState();
        return (bool)$model;
    }

    public function currentImage()
    {
        $model = $this->getState();
        if ($model) {
            $model = Media::find($model);
        }
        return $model ? $model->small_thumbnail : Media::uploadImage();
    }

    public function clearImageAction()
    {
        return Action::make('clear-image')
            ->action(function () {
                $this->state(null);
            });
    }

    public function pickImageAction()
    {
        return Action::make('pick-image')
            ->form(
                [
                    MediaTable::make('selected-image')->afterStateUpdated(function ($state) {
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
                    return (!$model || !$model->is_image);
                });
            })->action(function () {
                $livewire = $this->getLivewire();
                $currentFile = data_get($livewire, $this->getStatePath() . '_current_selection');
                $this->state($currentFile);
            });
        ;
    }
}
