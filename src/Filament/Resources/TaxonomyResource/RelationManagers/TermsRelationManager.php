<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\RelationManagers;

use Portable\FilaCms\Filament\Resources\TaxonomyTermResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TermsRelationManager extends RelationManager
{
    protected static string $relationship = 'terms';

    public function form(Form $form): Form
    {
        return TaxonomyTermResource::form($form);
    }

    public static function getResource()
    {
        return TaxonomyTermResource::class;
    }

    public function table(Table $table): Table
    {
        return TaxonomyTermResource::table($table);
    }
}
