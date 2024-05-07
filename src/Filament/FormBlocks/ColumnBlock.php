<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

class ColumnBlock extends AbstractFormBlock
{
    protected Closure|string|null $icon = 'heroicon-o-view-columns';

    public static function getBlockName(): string
    {
        return 'Columns';
    }

    public static function displayHtml($fieldData, $values): string
    {
        $count = isset($fieldData['column_count']) ? $fieldData['column_count'] : 2;
        $count = is_numeric($count) ? $count : 2;

        $html = '<table width="100%"><tr>';
        for($i = 0; $i < $count; $i++) {
            $fieldData['column_' . $i] = isset($fieldData['column_' . $i]) ? $fieldData['column_' . $i] : [];
            $html .= '<td width="' . (100 / $count) . '%">';
            $html .= FormBuilder::getDisplayFields($fieldData['column_' . $i], $values);
            $html .= '</td>';
        }
        $html .= '</html>';

        return $html;
    }

    public function getSchema(): Closure|array
    {
        return function (ColumnBlock $block, Get $get) {
            $data = $block->getState();
            $data = array_pop($data)['data'];

            $count = isset($data['column_count']) ? $data['column_count'] : 2;
            $count = is_numeric($count) ? $count : 2;

            return [
                TextInput::make('column_count')->label('Number of columns')->default(2)->required()->live(),
                Grid::make('columns')
                    ->columns($count)
                    ->schema(function (Get $get) {
                        $columns = [];
                        $count = $get('column_count');
                        $count = is_numeric($count) ? $count : 2;
                        for($i = 0; $i < $count; $i++) {
                            $columns[] = FormBuilder::make('column_' . $i)->columns(1);
                        }
                        return $columns;
                    })->live(),
            ];
        };
    }

    public static function getChildren($schema): Collection
    {
        $count = isset($schema['column_count']) ? $schema['column_count'] : 2;
        $count = is_numeric($count) ? $count : 2;

        $fields = collect();
        for($i = 0; $i < $count; $i++) {
            $kids = FormBuilder::getChildren(isset($schema['column_' . $i]) ? $schema['column_' . $i] : []);
            $fields = $fields->merge($kids);
        }

        return $fields;
    }

    public static function createField($fieldData, $readOnly = false): Component
    {
        $count = isset($fieldData['column_count']) ? $fieldData['column_count'] : 2;
        $count = is_numeric($count) ? $count : 2;

        $columns = [];
        for($i = 0; $i < $count; $i++) {
            $columns[] = Group::make(FormBuilder::getFields(isset($fieldData['column_' . $i]) ? $fieldData['column_' . $i] : [], $readOnly));
        }

        return Grid::make('columns')
            ->columns($count)
            ->schema($columns);
    }
}
