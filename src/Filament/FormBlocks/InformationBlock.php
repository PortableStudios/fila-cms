<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
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
            TextInput::make('field_name')
                ->label('Information')
                ->default('Information')
                ->hidden()
                ->readOnly()
                ->required()
                ->afterStateHydrated(function (TextInput $component, $state) {
                    if (empty($state)) {
                        $component->state('Information');
                    }
                }),
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

    public static function displayHtml($fieldData, $values): string
    {
        $value = static::displayValue($fieldData, $values);

        return '<div>' . $value . '</div>';
    }
}
