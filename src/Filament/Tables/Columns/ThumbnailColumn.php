<?php

namespace Portable\FilaCms\Filament\Tables\Columns;

use Filament\Tables\Columns\ImageColumn;

class ThumbnailColumn extends ImageColumn
{
    public function getState(): mixed
    {
        $thumbnail = $this->record->small_thumbnail;

        return $thumbnail;
    }
}
