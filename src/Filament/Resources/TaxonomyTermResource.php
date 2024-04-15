<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
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
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent')
                    ->options(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()
                            ->terms()
                            ->when($livewire->mountedTableActionRecord !== null, function ($query) use ($livewire) {
                                $query->whereNot('id', $livewire->mountedTableActionRecord)
                                    ->where(function ($q) use ($livewire) {
                                        $q->whereNot('parent_id', $livewire->mountedTableActionRecord)
                                          ->orWhereNull('parent_id');
                                    });

                            })
                            ->pluck('name', 'id')
                            ->toArray();
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('parent.name')
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
