<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class CheckboxBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-check-circle';
    protected static $componentClass = Checkbox::class;

    public static function getBlockName(): string
    {
        return 'Checkbox Field';
    }

    public function getSchema(): Closure|array
    {
        return [
            Grid::make('general')
                ->columns(2)
                ->schema([
                    TextInput::make('field_name')
                        ->label('Field Name')
                        ->default($this->getName())
                        ->required(),
                        ...static::getRequirementFields()
            ]),
                ];
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        if (isset($fieldData['required']) && $fieldData['required']) {
            $field->required();
        }

        return $field;
    }

    protected static function getRequirementFields(): array
    {
        return [
            Toggle::make('required')
                ->inline(false),
        ];
    }
}
