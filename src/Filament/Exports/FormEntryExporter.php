<?php

namespace Portable\FilaCms\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;
use Portable\FilaCms\Models\Form;
use Portable\FilaCms\Models\FormEntry;

class FormEntryExporter extends Exporter
{
    protected static ?string $model = FormEntry::class;
    protected static Form $_form;

    /**
     * @param  array<string, string>  $columnMap
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected Export $export,
        protected array $columnMap,
        protected array $options,
    ) {
        if (isset($options['form'])) {
            static::form($options['form']);
        }
    }

    public static function form(Form $form)
    {
        static::$_form = $form;

        return;
    }

    public static function getColumns(): array
    {
        // A flat collection of all form fields
        $allFields = FormBuilder::getChildren(static::$_form->fields);
        $columns = [];
        foreach ($allFields as $field) {
            $columns[] = ExportColumn::make($field->getName())
                ->label($field->getLabel())
                ->getStateUsing(function ($record) use ($field) {
                    $fieldName = trim($field->getName());
                    return isset($record->values[$fieldName]) ? $record->values[$fieldName] : '';
                });
        }

        return $columns;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your form entry export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
