<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;

abstract class AbstractTextBlock extends AbstractFormBlock
{
    public function getSchema(): Closure|array
    {
        $generalFields = [               
            TextInput::make('field_name')
                ->label('Field Name')
                ->default($this->getName())
                ->required()
        ];

        $typeSelector = static::getTypeSelector();
        if ($typeSelector) {
            $generalFields[] = $typeSelector;
        }

        return [
            Grid::make('general')
                ->columns(2)
                ->schema($generalFields),
                Grid::make('settings')
                    ->columns(3)
                    ->schema(function () {
                        return static::getRequirementFields();
                    }),
                ];
    }

    protected static function getTypeSelector()
    {
        return Select::make('text_type')
                        ->label('Text Type')
                        ->default('text')
                        ->options([
                            'text' => 'Text',
                            'number' => 'Number',
                            'email' => 'Email',
                            'password' => 'Password',
                            'url' => 'URL',
                            'tel' => 'Telephone',
                        ]);
    }

    protected static function createField($fieldData, $readOnly = false)
    {
        return (static::$componentClass)::make($fieldData['field_name'])
            ->default(isset($fieldData['default_value']) ? $fieldData['default_value'] : null);
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        if (isset($fieldData['max_length']) && method_exists($field, 'maxLength')) {
            $field->maxLength($fieldData['max_length']);
        }

        if (isset($fieldData['required']) && $fieldData['required'] && method_exists($field, 'required')) {
            $field->required();
        }

        if (isset($fieldData['text_type']) && method_exists($field, 'type')) {
            if ($fieldData['text_type'] == 'tel') {
                $field->tel();
            } else {
                $field->type($fieldData['text_type']);
            }
        }

        return $field;
    }

    protected static function getRequirementFields(): array
    {
        return [            
            FormBuilder::formFieldId(),
            Toggle::make('required')
                ->inline(false),
            TextInput::make('max_length')
                ->label('Max Length')
                ->integer(true),
            TextInput::make('default_value')
                ->label('Default Value'),                
        ];
    }
}
