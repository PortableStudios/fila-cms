<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Portable\FilaCms\Filament\Forms\Components\StatusBadge;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\TaxonomyResource;
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
                                ]),
                            Tabs\Tab::make('Taxonomies')
                                ->schema([
                                    ...static::getTaxonomyFields(),
                                ]),
                            Tabs\Tab::make('SEO')
                                ->schema([
                                    ...static::getSeoFields(),
                                ]),
                        ])
                        ->persistTabInQueryString()
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
                            Placeholder::make('publish_at_view')
                                ->label('Published')
                                ->visible(fn (?Model $record): bool => $record && $record->status === 'Published')
                                ->content(function (?Model $record): string {
                                    return $record->publish_at ?? '?';
                                }),
                            Placeholder::make('created_at_view')
                                ->label('Created')
                                ->visible(fn (?Model $record): bool => $record !== null)
                                ->content(function (?Model $record): string {
                                    return $record->created_at ?? '?';
                                }),
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

    public static function getSeoFields(): array
    {
        $seoFields = [
            Section::make('Search Engine Optimisation')
                ->compact()
                ->description('SEO metadata is automatically generated from your content. Override these fields to customise how your content appears in search engine results.')
                ->columns(12)
                ->schema([
                    Toggle::make('override_seo_title')
                        ->columnSpan(3)
                        ->inline(false)
                        ->label('Override Title')
                        ->live(),
                    TextInput::make('seo_title')
                        ->columnSpan(9)
                        ->label('Title')
                        ->hintColor('info')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Make sure your title is explicit and contains your most important keywords. Each page should have a unique title.')
                        ->placeholder(fn (Get $get): string => $get('title') ?? '')
                        ->disabled(fn (Get $get): bool => !$get('override_seo_title')),
                    Toggle::make('override_seo_description')
                        ->columnSpan(3)
                        ->inline(false)
                        ->label('Override Description')
                        ->live(),
                    Textarea::make('seo_description')
                        ->columnSpan(9)
                        ->maxLength(155)
                        ->label('Description')
                        ->hintColor('info')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'SEO descriptions allow you to influence how your web pages are described and displayed in search results. Ensure that all of your web pages have a unique meta description that is explicit and contains your most important keywords.')
                        ->helperText(function (?string $state): HtmlString {
                            $length = strlen($state);
                            $lengthStatus = match (true) {
                                $length > 160 => 'danger',
                                $length === 160 => 'success',
                                $length >= 140 => 'warning',
                                $length >= 80 => 'success',
                                default => 'gray',
                            };
                            return new HtmlString(
                                Str::of("<span style=\"color: rgba(var(--{$lengthStatus}-500),var(--tw-text-opacity))\">" . strlen($state) . '</span>')
                                    ->append(' / ')
                                    ->append(160 . ' ')
                                    ->append('characters')
                            );
                        })
                        ->maxLength(160)
                        ->reactive()
                        ->placeholder(fn (Get $get): string => $get('summary') ?? '')
                        ->disabled(fn (Get $get): bool => !$get('override_seo_description')),
                ]),
            Section::make('Open Graph')
                ->compact()
                ->description('Open Graph metadata is automatically generated from your content. Override these fields to customise how your content appears when a user posts a link on Facebook.')
                ->columns(12)
                ->schema([
                        Toggle::make('override_og_title')
                            ->columnSpan(3)
                            ->inline(false)
                            ->label('Override Title')
                            ->live(),
                        TextInput::make('og_title')
                            ->columnSpan(9)
                            ->label('Title')
                            ->hintColor('info')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Specifies the title of your content when users share this page on social media. A succinct and accurate title will grab the attention of users and encourage them to click on your content.')
                            ->placeholder(fn (Get $get): string => $get('title') ?? '')
                            ->disabled(fn (Get $get): bool => !$get('override_og_title')),
                        Toggle::make('override_og_description')
                            ->columnSpan(3)
                            ->inline(false)
                            ->label('Override Description')
                            ->live(),
                        Textarea::make('og_description')
                            ->columnSpan(9)
                            ->maxLength(155)
                            ->label('Description')
                            ->hintColor('info')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'This is shown below the Title when users share this page on social media. Provide a summary of the content and purpose of the page.')
                            ->placeholder(fn (Get $get): string => $get('summary') ?? '')
                            ->disabled(fn (Get $get): bool => !$get('override_og_description')),
                        Select::make('og_type')
                            ->columnStart(4)
                            ->columnSpan(9)
                            ->label('Type')
                            ->hintColor('info')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'The type should be the most specific type that describes your content. For example, if your content is a blog post, you should set the type to article.')
                            ->options([
                                'book' => 'Book',
                                'business.business' => 'Business',
                                'music.album' => 'Music Album',
                                'music.song' => 'Music Song',
                                'place' => 'Place',
                                'product' => 'Product',
                                'profile' => 'Profile',
                                'restaurant.restaurant' => 'Restaurant',
                                'video.other' => 'Video',
                                'website' => 'Website',
                            ])
                            ->default('website')
                            ->selectablePlaceholder(false),
                ]),
            Section::make('Robots')
                ->compact()
                ->description(str('If you do not want this page to be indexed by search engines, you can set the robots meta tag to **No Index**. **No Follow** will also prevent search engines from following links on this page.')->inlineMarkdown()->toHtmlString())
                ->columns(12)
                ->schema([
                        Select::make('robots')
                            ->columnSpanFull()
                            ->label('')
                            ->options([
                                'index, follow' => 'Index, Follow',
                                'noindex, follow' => 'No Index, Follow',
                                'index, nofollow' => 'Index, No Follow',
                                'noindex, nofollow' => 'No Index, No Follow',
                            ])
                            ->default('index, follow')
                            ->selectablePlaceholder(false)
                ]),
        ];

        return $seoFields;
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
        ];
        // @codeCoverageIgnoreEnd
    }
}
