<?php

namespace Portable\FilaCms\Filament\Traits;

use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Authorable;

trait HasAuthors
{
    public function authors()
    {
        return $this->morphToMany(Author::class, 'authorable');
    }

    public function authorable()
    {
        return $this->hasMany(Authorable::class, 'authorable_id')->where('authorable_type', static::class);
    }
}
