<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\TaxonomyResource;

class AbstractContentResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Content';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
            PublishedScope::class,
        ]);
    }

    public static function form(Form $form): Form
    {
        $fields = [
            Toggle::make('is_draft')
                ->label('Draft?')
                ->offIcon('heroicon-m-eye')
                ->onIcon('heroicon-m-eye-slash')->columnSpanFull(),
            TextInput::make('title')
                ->required(),
            Select::make('author_id')
                ->label('Author')
                ->options(Author::all()->pluck('display_name', 'id'))
                ->searchable(),
            DatePicker::make('publish_at')
                ->label('Publish Date'),
            DatePicker::make('expire_at')
                ->label('Expiry Date'),
            static::tiptapEditor(),
        ];

        TaxonomyResource::where('resource_class', static::class)->get()->each(function (TaxonomyResource $taxonomyResource) use (&$fields) {
            $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
            $fields[] = CheckboxList::make($fieldName.'_ids')
                ->label($taxonomyResource->taxonomy->name)
                ->options($taxonomyResource->taxonomy->terms->pluck('name', 'id'));
        });

        return $form->schema($fields);
    }

    public static function tiptapEditor($name = 'contents'): TiptapEditor
    {
        return TiptapEditor::make($name)
            ->profile('default')
            ->extraInputAttributes(['style' => 'min-height: 24rem;'])
            ->required()
            ->columnSpanFull();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Page $page): string => substr($page->contents, 0, 50).'...')
                    ->sortable(),
                TextColumn::make('author.display_name')->label('Author')
                    ->sortable(),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Pending' => 'warning',
                        'Published' => 'success',
                        'Expired' => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('createdBy.name')->label('Creator')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RevisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbstractContentResources::route('/'),
            'create' => Pages\CreateAbstractContentResource::route('/create'),
            'edit' => Pages\EditAbstractContentResource::route('/{record}/edit'),
        ];
    }
}
