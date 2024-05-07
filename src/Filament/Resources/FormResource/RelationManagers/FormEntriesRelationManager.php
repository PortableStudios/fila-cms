<?php

namespace Portable\FilaCms\Filament\Resources\FormResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Portable\FilaCms\Filament\Resources\FormEntryResource;

class FormEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    public function form(Form $form): Form
    {
        return FormEntryResource::form($form);
    }

    public function table(Table $table): Table
    {
        return FormEntryResource::table($table);
    }
}
