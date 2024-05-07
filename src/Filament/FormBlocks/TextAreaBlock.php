<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TextAreaBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-pencil-square';
    protected static $componentClass = Textarea::class;

    public static function getBlockName(): string
    {
        return 'Text Area';
    }

    public static function applyRequirementFields($field, $fieldData): Component
    {
        $field = parent::applyRequirementFields($field, $fieldData);
        $field->rows($fieldData['rows']);
        return $field;
    }

    public static function getRequirementFields(): array
    {
        $fields = parent::getRequirementFields();
        $fields[] = TextInput::make('rows')->default(5);

        return $fields;
    }
}
