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
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;

class DateTimeInputBlock extends AbstractTextBlock
{
    protected Closure|string|null $icon = 'heroicon-o-calendar-days';
    protected static $componentClass = TextInput::class;
    protected $date_type = DatePicker::class;

    public static function getBlockName(): string
    {
        return 'Date/Time Field';
    }

    protected static function createField($fieldData, $readOnly = false)
    {
        return ($fieldData['date_type'])::make($fieldData['field_name'])
            ->default(isset($fieldData['default_value']) ? $fieldData['default_value'] : null);
    }

    protected static function getTypeSelector($me = null)
    {
        return Select::make('date_type')
                        ->label('Date/Time Type')
                        ->default(DatePicker::class)
                        ->afterStateHydrated(function ($state) use ($me) {
                            $me->date_type = $state;
                        })
                        ->afterStateUpdated(function ($state) use ($me) {
                            $me->date_type = $state;
                        })
                        ->options([
                            DatePicker::class => 'Date',
                            TimePicker::class => 'Time',
                            DateTimePicker::class => 'Date & Time',
                        ])
                        ->live();
    }

    public function getSchema(): Closure|array
    {
        return function (DateTimeInputBlock $block, $state) {
            $data = array_pop($state)['data'];

            return [
                Grid::make('general')
                    ->columns(2)
                    ->schema([
                        TextInput::make('field_name')
                            ->label('Field Name')
                            ->default($this->getName())
                            ->required(),
                        static::getTypeSelector($this),

                ]),
                Grid::make('settings')
                    ->columns(3)
                    ->schema($this->getDateRequirementFields()),
                ];
        };
    }

    protected function getDateRequirementFields(): array
    {
        $default = (isset($this->date_type) ? $this->date_type : DatePicker::class)::make('default_value')
            ->label('Default Value');
        return [            
            FormBuilder::formFieldId(),
            Toggle::make('required')
                ->inline(false)->live(),
            $default
        ];
    }
}
