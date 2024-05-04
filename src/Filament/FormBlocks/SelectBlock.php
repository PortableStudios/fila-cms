<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class SelectBlock extends AbstractOptionsBlock
{
    protected Closure|string|null $icon = 'heroicon-o-chevron-up-down';
    protected static $componentClass = Select::class;

    public static function getBlockName(): string
    {
        return 'Select';
    }

    protected static function getRequirementFields(): array
    {
        return [
            Toggle::make('required')
                ->inline(false),
            Toggle::make('multiselect')
                ->inline(false)
                ->label('Multi-select'),
            Toggle::make('searchable')
                ->inline(false)
                ->label('Searchable'),
        ];
    }
}
