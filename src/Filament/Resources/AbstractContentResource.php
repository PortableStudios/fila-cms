<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;

use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\Forms\Components\StatusBadge;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\TaxonomyResource;
use RalphJSmit\Filament\Components\Forms as HandyComponents;

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
                                ->rules([
                                    function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $class = new static::$model();
                                            $data = ($class)->withoutGlobalScopes()->where('slug', $value)
                                                ->when($get('id') !== null, function ($query) use ($get) {
                                                    $query->whereNot('id', $get('id'));
                                                })
                                                ->first();
                                            if (is_null($data) === false) {
                                                $fail('The :attribute already exists');
                                            }
                                        };
                                    }
                                ])
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
                    TextInput::make('title')
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
                    Textarea::make('description')
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

        $only = [
            'title',
            'description',
            'robots',
        ];

        return [
            Group::make()
                ->schema($seoFields)
                ->afterStateHydrated(function (Group $component, ?Model $record) use ($only): void {
                    $data = $record?->seo?->only($only) ?: [];
                    if($record) {
                        $data['override_seo_title'] = $data['title'] !== $record?->title;
                        $data['override_seo_description'] = $data['description'] !== $record?->excerpt;
                    }
                    $component->getChildComponentContainer()->fill(
                        $data
                    );
                })
                ->statePath('seo')
                ->dehydrated(false)
                ->saveRelationshipsUsing(function (Model $record, array $state) use ($only) {
                    if($state['override_seo_title'] === false) {
                        $state['title'] = $record->title;
                    }

                    if($state['override_seo_description'] === false) {
                        $state['description'] = $record->excerpt;
                    }

                    $state = collect($state)->only($only)->map(fn ($value) => $value ?: null)->all();

                    if ($record->seo && $record->seo->exists) {
                        $record->seo->update($state);
                    } else {
                        $record->seo()->create($state);
                    }
                })
        ];

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
                    ->searchable()
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
                    ),
                SelectFilter::make('author')
                    ->multiple()
                    ->options(Author::all()->pluck('display_name', 'id'))
                    ->attribute('author_id'),
                SelectFilter::make('terms')
                    ->multiple()
                    ->relationship('terms', 'name'),
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
