<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Radio;

class RadioBlock extends AbstractOptionsBlock
{
    protected Closure|string|null $icon = 'heroicon-o-list-bullet';
    protected static $componentClass = Radio::class;

    public static function getBlockName(): string
    {
        return 'Radio Field';
    }

    protected static function getRequirementFields(): array
    {
        return [];
    }
}
