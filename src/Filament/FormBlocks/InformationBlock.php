<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Portable\FilaCms\Facades\FilaCms;

class InformationBlock extends AbstractFormBlock
{
    protected Closure|string|null $icon = 'heroicon-o-information-circle';

    public static function getBlockName(): string
    {
        return 'Information';
    }

    public function getSchema(): Closure|array
    {
        return [
            FilaCms::tipTapEditor('contents')
                ->label('Contents')
                ->required(),
            FormBuilder::formFieldId(),
        ];
    }

    public static function createField($fieldData, $readOnly = false): Component
    {
        $field = Placeholder::make('information')
            ->hiddenLabel(true)
            ->content(new HtmlString(tiptap_converter()->asHTML(isset($fieldData['contents']) ? $fieldData['contents'] : ['content' => ''])));

        return $field;
    }

    public static function displayValue($fieldData, $values): string
    {
        return tiptap_converter()->asHTML($fieldData['contents']);
    }
}
