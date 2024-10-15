<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Mansoor\FilamentVersionable\Table\RevisionsAction;
use Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\TaxonomyTerm;
use Portable\FilaCms\Facades\FilaCms;

class TaxonomyTermResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = TaxonomyTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        $owner = $form->getLivewire()->ownerRecord;

        return $form
            ->schema([
                FilaCms::maxTextInput('name', 255)
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) use ($owner) {
                        return $rule->where('taxonomy_id', $owner->id);
                    }),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent')
                    ->options(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()
                            ->terms()
                            ->when($livewire->mountedTableActionRecord !== null, function ($query) use ($livewire) {
                                $query->whereNot('id', (int)$livewire->mountedTableActionRecord);
                            })
                            ->when($livewire->mountedTableActionRecord !== null, function ($query) use ($livewire) {
                                $query->where(function ($query) use ($livewire) {
                                    $query->whereNull('parent_id')
                                        ->orWhere('parent_id', '<>', (int)$livewire->mountedTableActionRecord);
                                })->whereNull('parent_id');
                            })
                            ->when($livewire)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->disabled(function (RelationManager $livewire) {
                        $models = $livewire->getOwnerRecord()
                            ->terms()
                            ->where('parent_id', (int)$livewire->mountedTableActionRecord)
                            ->first();

                        return is_null($models) === false;
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
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, RelationManager $livewire) {
                        $term = TaxonomyTerm::where('id', $livewire->mountedTableActionRecord)->first();

                        // check if has taxonomyables
                        if ($term->taxonomyables->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Unable to delete term')
                                ->body('This term is currently in use')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, RelationManager $livewire) {
                            $hasTermWithContent = false;

                            foreach ($livewire->getSelectedTableRecords() as $key => $term) {
                                if ($term->taxonomyables->count() > 0) {
                                    $hasTermWithContent = true;
                                    break;
                                }
                            }

                            if ($hasTermWithContent) {
                                Notification::make()
                                    ->danger()
                                    ->title('Unable to delete terms')
                                    ->body('One or more terms selected is currently in use')
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Finish reordering' : 'Reorder terms'),
            )
            ->defaultSort('order')
            ->reorderable('order', auth()->user()->can('manage taxonomies'));
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
