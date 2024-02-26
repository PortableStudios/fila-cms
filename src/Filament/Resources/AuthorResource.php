<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Portable\FilaCms\Filament\Resources\AuthorResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Author;

class AuthorResource extends Resource
{
    use IsProtectedResource;

    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make()
                    ->schema([
                        TextInput::make('first_name')
                            ->label(fn (Get $get) => $get('is_individual') ? 'First Name' : 'Organization Name')
                            ->required()
                            ->autofocus(),
                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->visible(fn (Get $get) => $get('is_individual') ? true : false),
                    ])
                    ->columns(2),
                Toggle::make('is_individual')
                    ->onIcon('heroicon-m-user')
                    ->offIcon('heroicon-m-user-group')
                    ->default(true)
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')->sortable(),
                IconColumn::make('is_individual')->label('Category')
                    ->icon(fn (bool $state) => $state ? 'heroicon-m-user' : 'heroicon-m-user-group'),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
