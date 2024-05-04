<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;

class CheckboxListBlock extends AbstractOptionsBlock
{
    protected Closure|string|null $icon = 'heroicon-o-queue-list';
    protected static $componentClass = CheckboxList::class;

    public static function getBlockName(): string
    {
        return 'Checkbox List';
    }

    protected static function getRequirementFields(): array
    {
        return [
            Toggle::make('required')
                ->inline(false),
            Toggle::make('multiselect')
                ->inline(false)
                ->default(true)
                ->label('Multi-select'),
            Toggle::make('inline')
                ->inline(false)
                ->label('Display Inline'),
        ];
    }
}
