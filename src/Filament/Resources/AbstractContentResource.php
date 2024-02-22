<?php

namespace Portable\FilaCms\Filament\Resources;

use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Filament\Resources\PageResource\RelationManagers;
use Portable\FilaCms\Filament\Resources\PageResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;

use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

use FilamentTiptapEditor\TiptapEditor;

class AbstractContentResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
            PublishedScope::class
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                Toggle::make('is_draft')
                    ->label('Draft?')
                    ->offIcon('heroicon-m-eye')
                    ->onIcon('heroicon-m-eye-slash')
                    ->required(),
                DatePicker::make('publish_at')
                    ->label('Publish Date'),
                DatePicker::make('expire_at')
                    ->label('Expiry Date'),
                TiptapEditor::make('contents')
                    ->profile('default')
                    ->required()
                    ->columnSpanFull(),
                Select::make('author_id')
                    ->label('Author')
                    ->options(Author::all()->pluck('display_name', 'id'))
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Page $page): string => substr($page->contents, 0, 50) . '...')
                    ->sortable(),
                TextColumn::make('author.display_name')->label('Author')
                    ->sortable(),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft'     => 'gray',
                        'Pending'   => 'warning',
                        'Published' => 'success',
                        'Expired'   => 'danger',
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
