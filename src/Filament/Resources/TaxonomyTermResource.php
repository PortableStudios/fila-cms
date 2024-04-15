<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Mansoor\FilamentVersionable\Table\RevisionsAction;
use Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\TaxonomyTerm;

class TaxonomyTermResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = TaxonomyTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // RevisionsAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'revisions' => Pages\TaxonomyTermRevisions::route('/{record}/revisions'),
        ];
    }
}
