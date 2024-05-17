<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Filament\Forms\Components\Builder;
use Illuminate\Support\Collection;
use Portable\FilaCms\Facades\FilaCms;

class FormBuilder extends Builder
{
    public static function make(string $name): static
    {
        $availableBlocks = [];
        foreach(config('fila-cms.forms.blocks') as $block) {
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
        foreach($schema as $field) {
            $kids = FilaCms::getFormBlock($field['type'])::getFieldDefinitions($field['data']);
            $fields = $fields->merge($kids);
        }

        return $fields;
    }

    public static function getChildren($schema): Collection
    {
        $fields = collect();

        foreach($schema as $field) {
            $kids = FilaCms::getFormBlock($field['type'])::getChildren($field['data']);
            $fields = $fields->merge($kids);
        }

        return $fields;
    }

    public static function getFields($fieldData, $readOnly = false): array
    {
        $fields = [];
        foreach ($fieldData as $key => $field) {
            $fields[] = (FilaCms::getFormBlock($field['type']))::getField($field['data'], $readOnly);
        }

        return $fields;
    }

    public function displayHtml($fieldDefs, $values)
    {
        $html = '';
        foreach($fieldDefs as $fieldDef) {
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
}
