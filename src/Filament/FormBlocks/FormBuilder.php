<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Builder;
use Illuminate\Support\Facades\Blade;
use Portable\FilaCms\Data\DummyForm;
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

    public static function getDisplayFields($fieldData, $fieldValues): string
    {
        $fields = static::getFields($fieldData);
        $dummyForm = new DummyForm();

        $container = new ComponentContainer($dummyForm);
        $container->schema($fields);
        $container->fill($fieldValues);

        $fieldsHtml = '';

        $view = $container->render();

        $fieldsHtml = Blade::render($view, ['this' => $container]);

        $fieldsHtml = $container->render()->render();
        return $fieldsHtml;
    }
}
