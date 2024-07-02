<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Filament\Forms\Components\Builder;
use Illuminate\Support\Collection;
use Portable\FilaCms\Facades\FilaCms;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class FormBuilder extends Builder
{
    protected static $fieldId = 'field_id';

    public static function make(string $name): static
    {
        $availableBlocks = [];
        foreach (config('fila-cms.forms.blocks') as $block) {
            $availableBlocks[] = $block::make($block::getBlockName());
        }

        $static = parent::make($name)
            ->blocks($availableBlocks)
            ->addActionLabel('Add Field');

        return $static;
    }

    public static function getFieldDefinitions($schema): Collection
    {
        $fields = collect();
        foreach ($schema as $field) {
            $kids = FilaCms::getFormBlock($field['type'])::getFieldDefinitions($field['data']);
            $fields = $fields->merge($kids);
        }

        return $fields;
    }

    public static function getChildren($schema): Collection
    {
        $fields = collect();

        foreach ($schema as $field) {
            $kids = FilaCms::getFormBlock($field['type'])::getChildren($field['data']);
            $fields = $fields->merge($kids);
        }

        return $fields;
    }

    public static function getFields($fieldData, $readOnly = false): array
    {
        $fields = [];
        foreach ($fieldData as $key => $field) {

            if($readOnly) {
                $field['data']['required'] = false;
            }

            $fields[] = (FilaCms::getFormBlock($field['type']))::getField($field['data'], $readOnly);

        }

        return $fields;
    }

    public function displayHtml($fieldDefs, $values)
    {
        $html = '';
        foreach ($fieldDefs as $fieldDef) {
            $field = FilaCms::getFormBlock($fieldDef['type']);            
            $html .= $field::displayHtml($fieldDef['data'], $values);
        }
        return $html;
    }

    public static function getDisplayFields($fieldData, $fieldValues): string
    {
        $builder = static::make('display');
        return $builder->displayHtml($fieldData, $fieldValues);
    }

    public static function formFieldId(): TextInput
    {
        $fieldId = Str::slug(Str::random(10));

        return TextInput::make(static::$fieldId)
                ->label('FormBuilder:field_id')
                ->default($fieldId)
                // ->hidden()
                ->readOnly()
                ->required()
                ->afterStateHydrated(function (TextInput $component, $state) use ($fieldId) {                            
                    if(empty($state)) {
                        $component->state($fieldId);
                    }
                });
    }

    public static function getFormInputValue($fieldData, $values)
    {
        $fieldId = data_get($fieldData, static::$fieldId);
        return isset($values[$fieldId]) ? $values[$fieldId] : [];        
    }
}
