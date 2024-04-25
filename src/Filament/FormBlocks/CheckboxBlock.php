<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;

class CheckboxBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-battery-0';
    protected static $componentClass = Checkbox::class;

    public static function getBlockName(): string
    {
        return 'Checkbox Field';
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        if(isset($fieldData['required']) && $fieldData['required']) {
            $field->required();
        }

        return $field;
    }

    protected static function getRequirementFields(): array
    {
        return [
            Checkbox::make('required')
                ->inline(false)
                ->label('Required'),
        ];
    }

}
