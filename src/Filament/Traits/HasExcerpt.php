<?php

namespace Portable\FilaCms\Filament\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait HasExcerpt
{
    protected $excerptField = 'contents';

    public function excerpt(): Attribute
    {
        $excerpt = $this->{$this->excerptField};
        $content = $excerpt['content'];

        // get first content with "paragraph"
        $paragraph = '';
        foreach ($content as $key => $value) {
            if ($value['type'] === 'paragraph') {
                $paragraph = $value['content'][0]['text']; // take the first part
            }
        }

        return Attribute::make(function () use ($paragraph) {
            return Str::take(Str::of($paragraph)->stripTags(), 200);
        });
    }
}
