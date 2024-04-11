<?php

namespace Portable\FilaCms\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Users')
            ->columns([
                ViewColumn::make('name')
                    ->view('fila-cms::tables.columns.roles-user'),
                ViewColumn::make('created_at')
                    ->label('Creation Date')
                    ->view('fila-cms::tables.columns.created_at'),
            ]);
    }
}
