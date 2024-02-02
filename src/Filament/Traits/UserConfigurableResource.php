<?php

namespace Portable\FilaCms\Filament\Traits;

use Filament\Tables\Columns\TextColumn;

trait UserConfigurableResource
{
    protected static string $configKey;

    protected static $reflectionObject;

    protected static function getTableColumns()
    {
        $columns = [];

        // Then the rest
        foreach (config(static::$configKey . '.default_columns') as $defaultColumn) {
            if (in_array($defaultColumn, config(static::$configKey . '.exclude_fields'))) {
                continue;
            }


            $columns[] = static::makeColumn($defaultColumn);
        }

        return $columns;
    }

    protected static function makeColumn($name)
    {
        if (static::getReflection()->hasMethod($name)) {
            return TextColumn::make($name . '.name')
            ->distinctList();
        }

        return TextColumn::make($name)
            ->searchable()
            ->sortable();
    }

    protected static function getReflection()
    {
        if (static::$reflectionObject) {
            return static::$reflectionObject;
        }

        return static::$reflectionObject = new \ReflectionClass(static::$model);
    }
}
