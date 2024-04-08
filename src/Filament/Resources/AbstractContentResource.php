<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Portable\FilaCms\Filament\Forms\Components\StatusBadge;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\TaxonomyResource;
use RalphJSmit\Filament\Components\Forms as HandyComponents;
use RalphJSmit\Filament\SEO\SEO;
use Str;

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
            Group::make()
                ->schema([
                    Tabs::make()
                        ->tabs([
                            Tabs\Tab::make('Content')
                                ->schema([

                                    TextInput::make('title')
                                        ->columnSpanFull()
                                        ->required(),
                                    static::tiptapEditor()->output(\FilamentTiptapEditor\Enums\TiptapOutput::Json),
                                    SEO::make(['description']),
                                ]),
                            Tabs\Tab::make('Taxonomies')
                                ->schema([
                                    ...static::getTaxonomyFields(),
                                ]),
                        ]),
                ])
                ->columnSpan(2),
            Group::make()
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('slug')
                                ->maxLength(255),
                            Toggle::make('is_draft')
                                ->label('Draft?')
                                ->offIcon('heroicon-m-eye')
                                ->onIcon('heroicon-m-eye-slash')->columnSpanFull(),
                            Select::make('author_id')
                                ->label('Author')
                                ->options(Author::all()->pluck('display_name', 'id'))
                                ->searchable(),
                            View::make('fila-cms::components.hr'),
                            DatePicker::make('publish_at')
                                ->label('Publish Date')
                                ->live(),
                            DatePicker::make('expire_at')
                                ->label('Expiry Date'),
                        ])
                        ->columns(1),
                    Fieldset::make()
                        ->schema([
                            HandyComponents\CreatedAt::make()
                                ->label('Created'),
                            HandyComponents\UpdatedAt::make()
                                ->label('Updated'),
                            StatusBadge::make('status')
                                ->live()
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'Draft' => 'info',
                                    'Pending' => 'warning',
                                    'Published' => 'success',
                                    'Expired' => 'danger',
                                })
                                ->default('Draft'),

                        ])
                        ->columns(1)
                ])
                ->columnSpan(1),
        ];

        return $form->schema($fields)->columns(['lg' => 3]);
    }

    public static function getTaxonomyFields(): array
    {
        $taxonomyFields = [];
        TaxonomyResource::where('resource_class', static::class)->get()->each(function (TaxonomyResource $taxonomyResource) use (&$taxonomyFields) {
            $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
            $taxonomyFields[] = CheckboxList::make($fieldName . '_ids')
                ->label($taxonomyResource->taxonomy->name)
                ->options($taxonomyResource->taxonomy->terms->pluck('name', 'id'));
        });

        return $taxonomyFields;
    }

    public static function tiptapEditor($name = 'contents'): TiptapEditor
    {
        return TiptapEditor::make($name)
            ->profile('default')
            ->extraInputAttributes(['style' => 'min-height: 24rem;'])
            ->required()
            ->columnSpanFull()
            ->collapseBlocksPanel(true)
            ->output(TiptapOutput::Json);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Page $page): string => $page->excerpt)
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
                TernaryFilter::make('is_draft')
                    ->label('Draft')
                    ->attribute('is_draft')
                    ->nullable()
                    ->placeholder('All Records')
                    ->falseLabel('Non-Drafts Only')
                    ->trueLabel('Drafts Only')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_draft', true),
                        false: fn (Builder $query) => $query->where('is_draft', false),
                    )
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
        // @codeCoverageIgnoreStart
        return [
            'index' => Pages\ListAbstractContentResources::route('/'),
            'create' => Pages\CreateAbstractContentResource::route('/create'),
            'edit' => Pages\EditAbstractContentResource::route('/{record}/edit'),
            'revisions' => Pages\AbstractContentResourceRevisions::route('/{record}/revisions'),
        ];
        // @codeCoverageIgnoreEnd
    }
}
