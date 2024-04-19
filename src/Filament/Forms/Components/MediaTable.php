<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MediaTable extends Field
{
    protected string $view = 'fila-cms::filament.forms.components.media-table';
    public ?int $currentFolder;
    public ?int $currentFile;
}
