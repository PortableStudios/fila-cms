<?php

namespace Portable\FilaCms\Filament\Resources\TaxonomyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TermsRelationManager extends RelationManager
{
    protected static string $relationship = 'terms';

    public function form(Form $form): Form
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
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('parent.name')
                    ,
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
