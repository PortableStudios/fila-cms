<?php

namespace Portable\FilaCms\Filament\Resources\MenuResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Portable\FilaCms\Filament\Resources\MenuItemResource;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return MenuItemResource::form($form);
    }

    public static function getResource()
    {
        return MenuItemResource::class;
    }

    public function table(Table $table): Table
    {
        return MenuItemResource::table($table);
    }
}
