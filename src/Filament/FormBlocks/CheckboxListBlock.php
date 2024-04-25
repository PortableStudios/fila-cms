<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;

class CheckboxListBlock extends AbstractOptionsBlock
{
    protected Closure|string|null $icon = 'heroicon-o-battery-0';
    protected static $componentClass = CheckboxList::class;

    public static function getBlockName(): string
    {
        return 'Checkbox List';
    }

    protected static function getRequirementFields(): array
    {
        return [
            Checkbox::make('required')
                ->inline(false)
                ->label('Required'),
            Checkbox::make('multiselect')
                ->inline(false)
                ->default(true)
                ->label('Multi-select'),
            Checkbox::make('inline')
                ->inline(false)
                ->label('Display Inline'),
        ];
    }
}
