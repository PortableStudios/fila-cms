<?php

namespace Portable\FilaCms\Filament\Traits;

use Portable\FilaCms\Models\ShortUrl;

trait HasShortUrl
{
    public function shortUrls()
    {
        return $this->morphMany(ShortUrl::class, 'shortable');
    }
}
