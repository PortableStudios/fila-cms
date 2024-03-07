<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Support\Str;

class AbstractResource extends Resource
{
    public static function getTitleCaseModelLabel(): string
    {
        if (! static::hasTitleCaseModelLabel()) {
            return static::getModelLabel();
        }

        return Str::title(static::getModelLabel());
    }
}
