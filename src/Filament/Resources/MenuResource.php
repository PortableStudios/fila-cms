<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Portable\FilaCms\Filament\Resources\MenuResource\Pages;
use Portable\FilaCms\Filament\Resources\MenuResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Menu;
use Portable\FilaCms\Facades\FilaCms;

class MenuResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        $model = $form->model;

        return $form
            ->schema([
                FilaCms::maxTextInput('name', 255)->required(),
                FilaCms::maxTextInput('note', 255),
            ]);
    }

    public static function getModel(): string
    {
        return Menu::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable(),
                TextColumn::make('note')->label('Note'),
                TextColumn::make('items.count')->label('Items'),
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
            RelationManagers\ItemsRelationManager::class,
        ];
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
