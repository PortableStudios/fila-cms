<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

class DateTimeInputBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-calendar-days';
    protected static $componentClass = TextInput::class;

    public static function getBlockName(): string
    {
        return 'Date/Time Field';
    }

    protected static function createField($fieldData, $readOnly = false)
    {
        return ($fieldData['date_type'])::make($fieldData['field_name'])
            ->default(isset($fieldData['default_value']) ? $fieldData['default_value'] : null);
    }

    protected static function getTypeSelector()
    {
        return Select::make('date_type')
                        ->label('Date/Time Type')
                        ->default(DatePicker::class)
                        ->options([
                            DatePicker::class => 'Date',
                            TimePicker::class => 'Time',
                            DateTimePicker::class => 'Date & Time',
                        ])
                        ->live();
    }

    public function getSchema(): Closure|array
    {
        return function ($state) {
            $data = array_pop($state)['data'];
            return [
                Grid::make('general')
                    ->columns(2)
                    ->schema([
                        TextInput::make('field_name')
                            ->label('Field Name')
                            ->default($this->getName())
                            ->required(),
                        static::getTypeSelector(),

                ]),
                Grid::make('settings')
                    ->columns(3)
                    ->schema(static::getDateRequirementFields($data)),
                ];
        };
    }

    protected static function getDateRequirementFields($state): array
    {
        $default = $state['date_type']::make('default_value')
            ->label('Default Value');
        return [
            Toggle::make('required')
                ->inline(false),
            $default
        ];
    }
}
