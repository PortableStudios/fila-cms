<?php

namespace Portable\FilaCms\Contracts;

use Illuminate\Support\Str;

trait HasSlug
{
    protected function slugifyField(): string
    {
        return 'title';
    }

    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            if ($model->slug === null) {
                $model->slug = Str::slug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->slug === null) {
                $model->slug = Str::slug($model->title);
            }
        });
    }
}
