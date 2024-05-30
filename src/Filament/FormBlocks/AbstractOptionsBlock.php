<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

abstract class AbstractOptionsBlock extends AbstractFormBlock
{
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
                    Toggle::make('required')
                        ->inline(false),

                ]),
                Grid::make('settings')
                    ->columns(2)
                    ->schema(function () {
                        return static::getRequirementFields();
                    }),
                Repeater::make('options')
                    ->schema(function () {
                        return [
                            Grid::make('option')
                                ->columns(2)
                                ->schema([
                            TextInput::make('option_name')
                                ->label('Option Name')
                                ->required(),
                            TextInput::make('option_value')
                                ->label('Option Value')
                                ->required(),
                            ]),
                        ];
                    }),
                ];
    }

    protected static function createField($fieldData, $readOnly = false)
    {
        return (static::$componentClass)::make($fieldData['field_name'])
            ->options(static::getOptions($fieldData['options']))
            ->default(isset($fieldData['default_value']) ? $fieldData['default_value'] : null);
    }

    protected static function getOptions($optionsArray)
    {
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['option_name']] = $option['option_value'];
        }
        return $options;
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        if (isset($fieldData['required']) && $fieldData['required']) {
            $field->required();
        }

        if (isset($fieldData['multiselect']) && $fieldData['multiselect']) {
            $field->multiple();
        }

        if (isset($fieldData['inline']) && $fieldData['inline']) {
            $field->inline();
        }

        if (isset($fieldData['searchable']) && $fieldData['searchable']) {
            $field->searchable();
        }

        return $field;
    }

    protected static function getRequirementFields(): array
    {
        return [
            Checkbox::make('multiselect')
                ->inline(false)
                ->label('Multi-select'),
            Checkbox::make('inline')
                ->inline(false)
                ->label('Display Inline'),
        ];
    }

    public static function displayValue($fieldData, $values): string
    {
        $fieldName = data_get($fieldData, 'field_name');
        $value = isset($values[$fieldName]) ? $values[$fieldName] : [];
        if (!is_array($value)) {
            $value = [$value];
        }
        $options = data_get($fieldData, 'options', []);

        // Map value array keys to options
        $value = array_map(function ($val) use ($options) {
            return $options[$val] ?? $val;
        }, $value);

        return implode(", ", $value);
    }
}
