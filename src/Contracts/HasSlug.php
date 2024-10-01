<?php

namespace Portable\FilaCms\Contracts;

use Illuminate\Support\Str;

trait HasSlug
{
    protected function slugifyField(): string
    {
        return 'title';
    }

    protected function scopeSlugQuery($query, $slug)
    {
        $query = $query->where('slug', $slug);
        if($this->id) {
            $query = $query->where('id', '!=', $this->id);
        }
        return $query;
    }

    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->slug = $model->getNewSlug();
        });

        static::updating(function ($model) {
            $model->slug = $model->getNewSlug();
        });
    }

    protected function getNewSlug()
    {
        $newSlug = $this->slug ?? Str::slug($this->{$this->slugifyField()});

        // if there is a -clone already, then append 1 or increment
        $result = $this->scopeSlugQuery(static::withoutGlobalScopes(), $newSlug)->first();

        $count = 1;
        while ($result != null) {
            $incrementedSlug = $newSlug . '-' . $count;

            $result = $result = $this->scopeSlugQuery(static::withoutGlobalScopes(), $incrementedSlug)->first();
            $count++;

            if ($result == null) {
                $newSlug = $incrementedSlug;
                break;
            }
        }

        return $newSlug;
    }
}
