<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\CheckboxList;

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
        return [];
    }

}
