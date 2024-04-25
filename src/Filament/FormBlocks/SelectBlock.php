<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;

class SelectBlock extends AbstractOptionsBlock
{
    protected Closure|string|null $icon = 'heroicon-o-queue-list';
    protected static $componentClass = Select::class;

    public static function getBlockName(): string
    {
        return 'Select';
    }

    protected static function getRequirementFields(): array
    {
        return [
            Checkbox::make('required')
                ->inline(false)
                ->label('Required'),
            Checkbox::make('multiselect')
                ->inline(false)
                ->label('Multi-select'),
            Checkbox::make('searchable')
                ->inline(false)
                ->label('Searchable'),
        ];
    }
}
