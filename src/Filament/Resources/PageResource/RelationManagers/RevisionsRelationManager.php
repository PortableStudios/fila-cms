<?php

namespace Portable\FilaCms\Filament\Resources\PageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Actions\Action;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisionHistory';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('History')
            ->columns([
                ViewColumn::make('user_id')
                    ->view('fila-cms::tables.columns.user-responsible')
                    ->label('Editor')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('old_value')
                    ->view('fila-cms::tables.columns.diff-old')
                    ->label('Old Value'),
                ViewColumn::make('new_value')
                    ->view('fila-cms::tables.columns.diff-new')
                    ->label('New Value'),
                Tables\Columns\TextColumn::make('key')->label('Property')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date Changed')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Action::make('revert')
                    ->label('Revert')
                    ->button()
                    ->action(function($record) {
                        $model = $record->revisionable()->withoutGlobalScope(PublishedScope::class)->first();

                        $model->{$record->key} = $record->old_value;
                        $model->save();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
