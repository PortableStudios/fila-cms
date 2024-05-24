<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Portable\FilaCms\Filament\Resources\MenuResource\Pages;
use Portable\FilaCms\Filament\Resources\MenuResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\LinkCheck;

class LinkCheckResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = LinkCheck::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        $model = $form->model;

        return $form
            ->schema([
                
            ]);
    }

    public static function getModel(): string
    {
        return LinkCheck::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
            ])
            ->filters([

            ])
            ->actions([
                
            ])
            ->bulkActions([
                
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
