<?php

namespace Portable\FilaCms\Filament\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait HasExcerpt
{
    protected $excerptField = 'contents';

    public function excerpt(): Attribute
    {
        return Attribute::make(function () {
            return Str::take(Str::of($this->{$this->excerptField} ?? '')->stripTags(), 200);
        });

    }
}
