<?php

namespace Portable\FilaCms\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Portable\FilaCms\Facades\FilaCms;

class UserLoginsRelationManager extends RelationManager
{
    protected static string $relationship = 'logins';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FilaCms::maxTextInput('name', 255)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('created_at')
            ->columns([
                Tables\Columns\ViewColumn::make('created_at')
                    ->label('Login Time')
                    ->view('fila-cms::tables.columns.created_at'),
            ]);
    }
}
