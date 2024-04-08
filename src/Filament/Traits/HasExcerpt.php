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
        $content = is_array($excerpt) ? $excerpt['content'] : [];

        // get first content with "paragraph"
        $paragraph = '';
        foreach ($content as $key => $value) {
            if ($value['type'] === 'paragraph') {
                if (isset($value['content'])) {
                    foreach ($value['content'] as $key => $valueContent) {
                        // find first instance of text
                        if (isset($valueContent['text'])) {
                            $paragraph = $valueContent['text']; // take the first part
                            break;
                        }
                    }
                }
            }
        }

        return Attribute::make(function () use ($paragraph) {
            return Str::take(Str::of($paragraph)->stripTags(), 200);
        });
    }
}
