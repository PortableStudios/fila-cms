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
                ->getStateUsing(function ($record) use ($fieldKey, $fieldName) {
                    $values = $record->values['newEvent'] ?? $record->values;
                    $value = static::getEntryValue($record, (array) $values, $fieldKey, $fieldName);
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

    /**
     * Resolve a submitted value for a field in the current form definition.
     *
     * Entries key their values by field_id when the form had one at submission time and by
     * field_name otherwise, so a definition that has since lost its field_ids can no longer
     * address older entries. Fall back to the entry's own field snapshot, which still holds
     * the id the value was stored under.
     *
     * @param  array<string, mixed>  $values
     */
    protected static function getEntryValue(FormEntry $record, array $values, string $fieldKey, ?string $fieldName): mixed
    {
        foreach ([$fieldKey, $fieldName] as $key) {
            $key = trim((string) $key);

            if ($key !== '' && array_key_exists($key, $values)) {
                return $values[$key];
            }
        }

        $snapshotId = static::getSnapshotFieldId($record, $fieldName);

        return $snapshotId !== null ? ($values[$snapshotId] ?? '') : '';
    }

    /** Find the field_id this entry stored a field under, matching the snapshot on field_name. */
    protected static function getSnapshotFieldId(FormEntry $record, ?string $fieldName): ?string
    {
        $fieldName = trim((string) $fieldName);

        if ($fieldName === '' || empty($record->fields)) {
            return null;
        }

        foreach (FormBuilder::getFieldDefinitions($record->fields) as $field) {
            if (trim((string) Arr::get($field, 'data.field_name')) !== $fieldName) {
                continue;
            }

            $snapshotId = trim((string) Arr::get($field, 'data.' . FormBuilder::$fieldId));

            return $snapshotId === '' ? null : $snapshotId;
        }

        return null;
    }

    /**
     * Build the edit modal state, keyed by the state path each field currently uses
     * (its field_id when the definition has one, otherwise its field_name).
     *
     * @return array<string, mixed>
     */
    protected static function getEntryFormState(ModelsForm $form, FormEntry $record): array
    {
        $values = (array) ($record->values['newEvent'] ?? $record->values);
        $state = $values;

        foreach (FormBuilder::getFieldDefinitions($form->fields) as $field) {
            $fieldName = Arr::get($field, 'data.field_name');
            $fieldId = Arr::get($field, 'data.' . FormBuilder::$fieldId);

            $statePath = trim((string) ($fieldId ?: $fieldName));

            if ($statePath === '') {
                continue;
            }

            $state[$statePath] = static::getEntryValue($record, $values, $statePath, $fieldName);
        }

        return $state;
    }

    public static function table(Table $table): Table
    {

        $ownerRecord = $table->getLivewire()->ownerRecord;

        $columns = static::getColumns($ownerRecord);

        return $table
            ->defaultSort('created_at', 'desc')
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
                Tables\Actions\EditAction::make()->fillForm(function ($record) use ($ownerRecord) {
                    return [
                        'status' => $record->status,
                        ...static::getEntryFormState($ownerRecord, $record),
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
