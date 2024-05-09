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

    public static function createField($fieldData, $readOnly = false)
    {
        return FilaCms::tipTapEditor($fieldData['field_name'])
            ->default($fieldData['default_value']);
    }

    protected static function getTypeSelector()
    {
        return null;
    }

    public static function displayHtml($fieldData, $values): string
    {
        $field = static::getField($fieldData);
        $value = isset($values[$field->getName()]) ? $values[$field->getName()] : '';

        if(is_array($value)) {
            $value = tiptap_converter()->asHTML($value);
        }

        return '<div><strong>' . $field->getLabel() . '</strong>: ' . $value . '</div>';
    }
}
