<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Portable\FilaCms\Models\Media;

class ImagePicker extends Field
{
    protected string $view = 'fila-cms::filament.forms.components.image-picker';
    public ?int $currentFolder;
    public ?int $currentFile;

    protected ?bool $isLive = true;

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
}
