<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Portable\FilaCms\Filament\Exports\FormEntryExporter;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;
use Portable\FilaCms\Filament\FormBlocks\InformationBlock;
use Portable\FilaCms\Filament\Resources\FormEntryResource\Actions\ExportBulkAction;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Form as ModelsForm;
use Portable\FilaCms\Models\FormEntry;

class FormEntryResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = FormEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        $owner = $form->getLivewire()->ownerRecord;
        $fields = [
            Forms\Components\Select::make('status')
                ->options(
                    [
                    'New' => 'New',
                    'Open' => 'Open',
                    'Closed' => 'Closed',
                    ]
                ),
                Group::make(FormBuilder::getFields($owner->fields, true))->columnSpanFull(),
        ];

        return $form->schema($fields);
    }

    public static function getColumns(ModelsForm $form): array
    {
        $columns = [
            TextColumn::make('status')
                ->badge()
                ->color(function ($state) {
                    return match ($state) {
                        'New' => 'info',
                        'Open' => 'warning',
                        'Closed' => 'success',
                        default => 'info',
                    };
                })
                ->label('Status')
                ->sortable(true),
        ];

        // A flat collection of all form fields
        $allFields = FormBuilder::getFieldDefinitions($form->fields);

        $formBuilderFieldId = FormBuilder::$fieldId;

        foreach ($allFields as $field) {
            // don't show information blocks in the table
            if (Arr::get($field, 'type') === InformationBlock::getBlockName()) {
                continue;
            }

            $fieldName = Arr::get($field, 'data.field_name', null);
            $fieldId = Arr::get($field, 'data.' . $formBuilderFieldId, null);

            $fieldKey = trim((string) ($fieldId ?: $fieldName));

            // Skip if we have no usable key
            if ($fieldKey === '') {
                continue;
            }

            $columnName = 'field_' . md5($fieldKey);

            $columns[] = Tables\Columns\TextColumn::make($columnName)
                ->label($fieldName)
                ->getStateUsing(function ($record) use ($fieldKey) {
                    $values = $record->values['newEvent'] ?? $record->values;
                    $value = isset($values[$fieldKey]) ? $values[$fieldKey] : '';
                    if (is_array($value)) {
                        try {
                            $value = tiptap_converter()->asText($value);
                        } catch (\Exception $e) {
                            return implode(", ", $value);
                        }
                    }
                    return $value;
                })
                ->searchable(true, function ($query, $search) use ($fieldKey) {
                    $query->where('values->' . $fieldKey, 'like', '%' . $search . '%');
                })
                ->words(10);
        }

        if ($form->only_for_logged_in) {
            // show user who created it
            $columns[] = Tables\Columns\ViewColumn::make('')
                    ->label('Submitted By')
                    ->view('fila-cms::tables.columns.created_by');
        }

        $columns[] = Tables\Columns\ViewColumn::make('created_at')
                    ->label('Submitted Time')
                    ->view('fila-cms::tables.columns.created_at');

        return $columns;
    }

    public static function table(Table $table): Table
    {

        $columns = static::getColumns($table->getLivewire()->ownerRecord);

        return $table
            ->recordTitleAttribute('created_at')
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'New' => 'New',
                        'Open' => 'Open',
                        'Closed' => 'Closed',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()->fillForm(function ($record) {
                    return [
                        'status' => $record->status,
                        ...($record->values['newEvent'] ?? $record->values),
                    ];
                }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(FormEntryExporter::class)
                        ->options([
                            'form' => $table->getLivewire()->ownerRecord,
                        ])
                        ->ownerRecord($table->getLivewire()->ownerRecord),
                    Tables\Actions\BulkAction::make('mark-as-new')
                        ->label('Mark as New')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'New']);
                            }
                        }),
                    Tables\Actions\BulkAction::make('mark-as-open')
                    ->label('Mark as Open')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->update(['status' => 'Open']);
                        }
                    }),
                    Tables\Actions\BulkAction::make('mark-as-closed')
                    ->label('Mark as Closed')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->update(['status' => 'Closed']);
                        }
                    }),
                ]),
            ])
            ->columns($columns);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [

        ];
    }
}
