<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Component;
use Illuminate\Support\Collection;

abstract class AbstractFormBlock extends Block
{
    abstract public static function getBlockName(): string;
    abstract protected static function createField($fieldData, $readOnly = false);
    abstract public function getSchema(): Closure|array;

    protected static $componentClass;

    public static function make(string $name): static
    {
        $static = parent::make($name);
        $static->schema($static->getSchema());

        return $static;
    }

    public static function getChildren($schema): Collection
    {
        $field = static::getField($schema);

        $collection = collect([$field]);
        return $collection;
    }

    public static function getField($fieldData, $readOnly = false): Component
    {
        // Validate field data

        $field = static::createField($fieldData, $readOnly);
        $field = static::applyRequirementFields($field, $fieldData);
        if($readOnly && method_exists($field, 'readOnly')) {
            $field->readOnly();
        }

        return $field;
    }

    public static function displayHtml($fieldData, $values): string
    {
        $field = static::getField($fieldData);
        $value = isset($values[$field->getName()]) ? $values[$field->getName()] : '';

        return '<div><strong>' . $field->getLabel() . '</strong>: ' . $value . '</div>';
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        return $field;
    }
}
