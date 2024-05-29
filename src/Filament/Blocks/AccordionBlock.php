<?php

namespace Portable\FilaCms\Filament\Blocks;

use Filament\Forms\Components\TextInput;
use FilamentTiptapEditor\TiptapBlock;
use Portable\FilaCms\Facades\FilaCms;

class AccordionBlock extends TiptapBlock
{
    public string $width = 'lg';
    public string $preview = 'fila-cms::blocks.previews.accordion';

    public string $rendered = 'fila-cms::blocks.rendered.accordion';

    public ?string $icon = 'heroicon-o-window';

    public function getModalWidth(): string
    {
        return '3xl';
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('heading')
                ->required(),
            FilaCms::tipTapEditor('details')->required(),
        ];
    }
}
