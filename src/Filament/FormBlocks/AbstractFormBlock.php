<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Component;

abstract class AbstractFormBlock extends Block
{
    abstract public static function getBlockName(): string;
    abstract protected static function createField($fieldData);
    abstract public function getSchema(): Closure|array;

    protected static $componentClass;

    public static function make(string $name): static
    {
        $static = parent::make($name);
        $static->schema($static->getSchema());

        return $static;
    }

    public static function getField($fieldData): Component
    {
        // Validate field data

        $field = static::createField($fieldData);
        $field = static::applyRequirementFields($field, $fieldData);

        return $field;
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        return $field;
    }
}
