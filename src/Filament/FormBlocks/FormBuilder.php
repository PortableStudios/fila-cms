<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Filament\Forms\Components\Builder;
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

    public static function getFields($fieldData): array
    {
        $fields = [];
        foreach ($fieldData as $key => $field) {
            $fields[] = (FilaCms::getFormBlock($field['type']))::getField($field['data']);
        }

        return $fields;

    }
}
