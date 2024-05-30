<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\TextInput;

class TextInputBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-battery-0';
    protected static $componentClass = TextInput::class;

    public static function getBlockName(): string
    {
        return 'Text Field';
    }
}
