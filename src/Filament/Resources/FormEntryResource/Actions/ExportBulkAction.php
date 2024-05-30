<?php

namespace Portable\FilaCms\Filament\Resources\FormEntryResource\Actions;

use Filament\Tables\Actions\ExportBulkAction as ActionsExportBulkAction;
use Portable\FilaCms\Models\Form;

class ExportBulkAction extends ActionsExportBulkAction
{
    protected $_ownerRecord;

    public function ownerRecord(Form $form)
    {
        $this->_ownerRecord = $form;

        return $this;
    }

    public function getExporter(): string
    {
        $exporter = parent::getExporter();
        $exporter::form($this->_ownerRecord);
        return $exporter;
    }
}
