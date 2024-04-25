<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Portable\FilaCms\Facades\FilaCms;

class RichTextBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-newspaper';

    public static function getBlockName(): string
    {
        return 'Rich Text';
    }

    public static function createField($fieldData)
    {
        return FilaCms::tipTapEditor($fieldData['field_name'])
            ->default($fieldData['default_value']);
    }
}
