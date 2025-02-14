<?php

namespace Portable\FilaCms\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Forms\Components\StatusBadge;
use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Livewire\ContentResourceList;
use Portable\FilaCms\Livewire\ContentResourceShow;
use Portable\FilaCms\Models\AbstractContentModel;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\TaxonomyResource;
use RalphJSmit\Filament\Components\Forms as HandyComponents;
use Portable\FilaCms\Filament\Tables\Actions\RestoreAction;
use Portable\FilaCms\Filament\Actions\CloneAction;
use Illuminate\Support\Facades\Schema;

class AbstractContentResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Content';

    public static function getFrontendRoutePrefix()
    {
        return static::getRoutePrefix();
    }

    public static function getFrontendShowRoute()
    {
        return static::getFrontendRoutePrefix() . '.{slug}';
    }

    public static function getFrontendIndexRoute()
    {
        return static::getFrontendRoutePrefix() . '.index';
    }

    public static function registerIndexRoute()
    {
        return true;
    }

    public static function registerShowRoute()
    {
        return true;
    }

    public static function getFrontendIndexComponent()
    {
        return ContentResourceList::class;
    }

    public static function getFrontendShowComponent()
    {
        return ContentResourceShow::class;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
            PublishedScope::class,
        ]);
    }

    public static function getContentTab()
    {
        return Tabs\Tab::make('Content')
            ->schema([
                TextInput::make('title')
                    ->columnSpanFull()
                    ->required(),
                FilaCms::tiptapEditor('contents'),
            ]);
    }

    public static function getMainTabs()
    {
        $tabs = [
            static::getContentTab(),
            Tabs\Tab::make('Taxonomies')
                ->schema(static::getTaxonomyFields()),
            Tabs\Tab::make('SEO')
                ->schema(static::getSeoFields()),
            Tabs\Tab::make('Short URLs')
                ->schema(static::getVanityURLFields()),
            Tabs\Tab::make('Content Permission')
                ->schema(static::getRoleRestrictionFields()),
        ];

        return $tabs;
    }

    public static function getSidebarFieldSection()
    {
        return Section::make()
        ->schema([
            FilaCms::maxTextInput('slug', 255)
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
                ]),
            Toggle::make('is_draft')
                ->label('Draft?')
                ->offIcon('heroicon-m-eye')
                ->onIcon('heroicon-m-eye-slash')->columnSpanFull(),
            Select::make('authors')
                ->label('Author(s)')
                ->relationship()
                ->getSearchResultsUsing(function ($search) {
                    return Author::where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->limit(50)->get()->pluck('display_name', 'id')->toArray();
                })
                ->getOptionLabelFromRecordUsing(function (Author $author) {
                    return $author->display_name;
                })
                ->multiple()
                ->createOptionForm(static::getCreateAuthorForm())
                ->createOptionUsing(function (array $data) {
                    return Author::create($data)->getKey();
                })
                ->searchable(),
            View::make('fila-cms::components.hr'),
            DatePicker::make('publish_at')
                ->label('Publish Date')
                ->live(),
            DatePicker::make('expire_at')
                ->label('Expiry Date'),
        ])
        ->columns(1);
    }

    public static function getSidebarInfoSection()
    {
        return Fieldset::make()
                ->schema([
                    Placeholder::make('creator')
                         ->content(fn (?AbstractContentModel $record): string => $record?->createdBy?->name ?? 'Unknown'),
                    HandyComponents\CreatedAt::make()
                        ->label('Created'),
                    HandyComponents\UpdatedAt::make()
                        ->label('Updated'),
                    StatusBadge::make('status')
                        ->live()
                        ->badge()
                        ->color(fn (string $state): mixed => match ($state) {
                            'Draft' => 'info',
                            'Pending' => 'warning',
                            'Published' => 'success',
                            'Expired' => 'danger',
                            'Deleted' => \Filament\Support\Colors\Color::Indigo,
                        })
                        ->default('Draft'),
                ])
                ->columns(1);
    }

    public static function getSidebar()
    {
        return [ static::getSidebarFieldSection(), static::getSidebarInfoSection()];
    }

    protected static function getCreateAuthorForm()
    {
        return AuthorResource::getFormFields();
    }

    public static function form(Form $form): Form
    {
        $fields = [
            Group::make()
                ->schema([
                    Tabs::make()
                        ->tabs(static::getMainTabs())
                        ->persistTabInQueryString()
                ])
                ->columnSpan(2),
            Group::make()
                ->schema(static::getSidebar())
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
                        ->live()
                        ->maxLength(60)
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Make sure your title is explicit and contains your most important keywords. Each page should have a unique title.')
                        ->placeholder(fn (Get $get): string => $get('title') ?? '')
                        ->helperText(function (?string $state): HtmlString {
                            $length = strlen($state);
                            $lengthStatus = match (true) {
                                $length > 60 => 'danger',
                                $length === 60 => 'success',
                                $length >= 40 => 'warning',
                                $length >= 30 => 'success',
                                default => 'gray',
                            };
                            return new HtmlString(
                                Str::of("<span style=\"color: rgba(var(--{$lengthStatus}-500),var(--tw-text-opacity))\">" . strlen($state) . '</span>')
                                    ->append(' / ')
                                    ->append(60 . ' ')
                                    ->append('characters')
                            );
                        })
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
                            ->disabled(function (Get $get, Set $set) {
                                if (count($get('data.roleRestrictions.role_id', true)) > 0) {
                                    $set('robots', 'noindex, nofollow');
                                    return true;
                                }
                                return false;
                            })
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
                    if ($record) {
                        $data['override_seo_title'] = $data['title'] !== Str::limit($record?->title, 57);
                        $data['override_seo_description'] = $data['description'] !== Str::limit($record?->excerpt, 157);
                    }
                    $component->getChildComponentContainer()->fill(
                        $data
                    );
                })
                ->statePath('seo')
                ->dehydrated(false)
                ->saveRelationshipsUsing(function (Model $record, Group $group, array $state) use ($only) {
                    $formData = $group->getContainer()->getLivewire()->data;
                    $record->title = $formData['title'];
                    $record->contents = $formData['contents'];
                    if ($state['override_seo_title'] === false) {
                        $state['title'] = Str::limit($record->title, 57);
                    }

                    if ($state['override_seo_description'] === false) {
                        $state['description'] = Str::limit($record->excerpt, 157);
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

    public static function getVanityURLFields(): array
    {
        $prefixUrl = implode('/', [rtrim(config('app.url'), '/'), trim(config('fila-cms.short_url_prefix'), '/'), '' ]);
        $vanityURLFields = [
            Section::make('Legacy or Vanity URLs')
                ->compact()
                ->description('When you have a piece of content with a long URL (slug) you can use this tool to create a shorter, more user-friendly URL for marketing and online activities. When a user visits the supplied URL they will be redirected to the original entry.')
                ->schema([
                    Repeater::make('shortUrls')
                        ->relationship()
                        ->reorderable(false)
                        ->defaultItems(0)
                        ->collapsed()
                        ->addActionLabel('Add new URL')
                        ->schema([
                            TextInput::make('url')
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->live(onBlur: true)
                                ->required()
                                ->hintColor('info')
                                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'If the input provided does not conform to URL standards, the system will automatically sanitize it by removing any special characters that are not compatible with URLs.')
                                ->prefix($prefixUrl)
                                ->suffixIcon('heroicon-m-globe-alt'),
                            Select::make('redirect_status')
                                ->hintColor('info')
                                ->hintIcon('heroicon-m-question-mark-circle', tooltip: "When you set up a 301 permanent redirect, any changes you make to enable or disable it won't apply until you clear your browser's cache.")
                                ->options([
                                    '301' => '301 - Permanent',
                                    '302' => '302 - Temporary'
                                ])
                                ->default(301)
                                ->required(),
                            Toggle::make('enable')
                                ->live(onBlur: true)
                                ->offIcon('heroicon-m-eye-slash')
                                ->onIcon('heroicon-m-eye'),
                            TextInput::make('hits')
                                ->label('Total views')
                                ->default(0)
                                ->integer()
                                ->readOnly()

                        ])
                        ->itemLabel(function (array $state) use ($prefixUrl) {
                            $label = '';
                            if ($state['enable'] === false) {
                                $label .= '[Disabled] - ';
                            }
                            $label .= $prefixUrl . Str::slug($state['url'] ?? null);
                            return $label;
                        })
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['url'] = Str::slug($data['url']);
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                            $data['url'] = Str::slug($data['url']);
                            return $data;
                        })
                ]),
        ];


        return [
            Group::make()
                ->schema($vanityURLFields)
                ->statePath('shortUrls')
                ->dehydrated(false)
        ];
    }

    public static function getRoleRestrictionFields(): array
    {
        $roleRestrictionFields = [
            Section::make('Allowed Roles')
                ->compact()
                ->description('You can limit who can see particular types of content by their role. Choose from the list of roles below to provide exclusive access to this content.  If a role is selected, the content is hidden to non-authenticated viewers.')
                ->schema([
                    Select::make('role_id')
                        ->relationship(name: 'roles', titleAttribute: 'name')
                        ->searchable(false)
                        ->preload()
                        ->multiple()
                        ->live(),
                ])
        ];

        return [
            Group::make()
                ->schema($roleRestrictionFields)
                ->statePath('roleRestrictions')
                ->dehydrated(false)
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Page $page): string => Str::take($page->excerpt, 35))
                    ->searchable()
                    ->limit(35)
                    ->sortable(),
                TextColumn::make('authors')
                    ->label('Author(s)')
                    ->placeholder('No author')
                    ->getStateUsing(function (Model $record) {
                        return $record->authors->pluck('display_name');
                    })
                    ->limitList(2),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $state): mixed => match ($state) {
                        'Draft' => 'gray',
                        'Pending' => 'warning',
                        'Published' => 'success',
                        'Expired' => 'danger',
                        'Deleted' => \Filament\Support\Colors\Color::Indigo,
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->select()
                            ->selectSub(function ($query) {
                                $query->selectRaw('CASE
                                WHEN `deleted_at` IS NOT NULL THEN "deleted"
                                WHEN `is_draft` THEN "draft"
                                WHEN `publish_at` > now() THEN "pending"
                                WHEN `publish_at` < now() AND `expire_at` < now() THEN "expired"
                                WHEN `publish_at` < now() AND (`expire_at` > now() or `expire_at` IS NULL) THEN "published"
                            END');
                            }, 'status')
                        ->orderBy('status', $direction);
                    }),
                TextColumn::make('createdBy.name')->label('Creator')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('All Records')
                    ->options([
                        'draft'     => 'Draft',
                        'pending'   => 'Pending',
                        'published' => 'Published',
                        'expired'   => 'Expired',
                        'deleted'   => 'Deleted',
                    ])
                    ->multiple()
                    ->default(['draft', 'pending', 'published', 'expired'])
                    ->query(function (Builder $query, $data) {
                        $query->withoutGlobalScopes();

                        $query->where(function ($query) use ($data) {
                            foreach ($data['values'] as $key => $value) {
                                switch ($value) {
                                    case 'draft':
                                        $query->orWhere(function ($query) {
                                            $query->where('is_draft', true)->whereNull('deleted_at');
                                        });
                                        break;
                                    case 'pending':
                                        $query->orWhere(function ($query) {
                                            $query->where('is_draft', false)
                                                ->where('publish_at', '>', now())
                                                ->where(function ($query) {
                                                    $query->whereNull('expire_at')
                                                        ->orWhere('expire_at', '>', now());
                                                })
                                                ->whereNull('deleted_at');
                                        });
                                        break;
                                    case 'published':
                                        $query->orWhere(function ($query) {
                                            $query->where('is_draft', false)
                                                ->where('publish_at', '<', now())
                                                ->where(function ($query) {
                                                    $query->whereNull('expire_at')
                                                        ->orWhere('expire_at', '>', now());
                                                })
                                                ->whereNull('deleted_at');
                                        });
                                        break;
                                    case 'expired':
                                        $query->orWhere(function ($query) {
                                            $query->where('is_draft', false)
                                                ->where('publish_at', '<', now())
                                                ->where('expire_at', '<', now())
                                                ->whereNull('deleted_at');
                                        });
                                        break;
                                    case 'deleted':
                                        $query->orWhere(function ($query) {
                                            $query->whereNotNull('deleted_at');
                                        });
                                        break;
                                }
                            }
                        });
                    }),
                static::getAuthorFilter(),
                SelectFilter::make('terms')
                    ->multiple()
                    ->relationship('terms', 'name'),
                Tables\Filters\Filter::make('publish_at')
                    ->form([
                        Fieldset::make('Published')
                            ->schema([
                                DatePicker::make('publish_from')->label('From'),
                                DatePicker::make('publish_to')->label('To'),
                            ])
                            ->columns(2)
                    ])
                    ->columnSpan(2)
                    ->indicateUsing(function (array $data): ?string {
                        $texts = [];
                        if ($data['publish_from']) {
                            $texts[] = 'from: ' . Carbon::parse($data['publish_from'])->format('Y-m-d');
                        }
                        if ($data['publish_to']) {
                            $texts[] = 'to: ' . Carbon::parse($data['publish_to'])->format('Y-m-d');
                        }

                        if (count($texts) === 0) {
                            return null;
                        }
                        array_unshift($texts, 'Published');
                        return implode(' ', $texts);
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['publish_from'], function ($query) use ($data) {
                            $query->where('publish_at', '>=', Carbon::parse($data['publish_from'])->startOfDay());
                        })->when($data['publish_to'], function ($query) use ($data) {
                            $query->where('publish_at', '<=', Carbon::parse($data['publish_to'])->endOfDay());
                        });
                    }),
                Tables\Filters\Filter::make('expire_at')
                    ->form([
                        Fieldset::make('Expiry')
                            ->schema([
                                DatePicker::make('expire_from')->label('From'),
                                DatePicker::make('expire_to')->label('To'),
                            ])
                            ->columns(2)
                    ])
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['expire_from'], function ($query) use ($data) {
                            $query->where('expire_at', '>=', Carbon::parse($data['expire_from'])->startOfDay());
                        })->when($data['expire_to'], function ($query) use ($data) {
                            $query->where('expire_at', '<=', Carbon::parse($data['expire_to'])->endOfDay());
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $texts = [];
                        if ($data['expire_from']) {
                            $texts[] = 'from: ' . Carbon::parse($data['expire_from'])->format('Y-m-d');
                        }
                        if ($data['expire_to']) {
                            $texts[] = 'to: ' . Carbon::parse($data['expire_to'])->format('Y-m-d');
                        }

                        if (count($texts) === 0) {
                            return null;
                        }

                        array_unshift($texts, 'Expires');
                        return implode(' ', $texts);
                    }),
                Tables\Filters\Filter::make('updated_at')
                    ->form([
                        Fieldset::make('Modified')
                            ->schema([
                                DatePicker::make('updated_from')->label('From'),
                                DatePicker::make('updated_to')->label('To'),
                            ])
                            ->columns(2)
                    ])
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['updated_from'], function ($query) use ($data) {
                            $query->where('updated_at', '>=', Carbon::parse($data['updated_from'])->startOfDay());
                        })->when($data['updated_to'], function ($query) use ($data) {
                            $query->where('updated_at', '<=', Carbon::parse($data['updated_to'])->endOfDay());
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $texts = [];
                        if ($data['updated_from']) {
                            $texts[] = 'from: ' . Carbon::parse($data['updated_from'])->format('Y-m-d');
                        }
                        if ($data['updated_to']) {
                            $texts[] = 'to: ' . Carbon::parse($data['updated_to'])->format('Y-m-d');
                        }

                        if (count($texts) === 0) {
                            return null;
                        }

                        array_unshift($texts, 'Modified');
                        return implode(' ', $texts);
                    }),
            ])
            ->filtersFormColumns(2)
            ->filtersFormWidth(MaxWidth::ExtraLarge)
            ->filtersTriggerAction(
                fn (Action $action) => $action
                        ->button()
                        ->label('Filter'),
            )
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if (Schema::hasColumn($record->getTable(), 'is_draft')) {
                            $record->update([
                                'is_draft' => true
                            ]);
                        }
                    }),
                Tables\Actions\ForceDeleteAction::make(),
                RestoreAction::make(),
                Tables\Actions\EditAction::make(),
                CloneAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    protected static function getAuthorFilter()
    {
        return SelectFilter::make('author')
            ->multiple()
            ->getSearchResultsUsing(function ($search) {
                return Author::where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")->get()->pluck('display_name', 'id')->toArray();
            })
            ->getOptionLabelFromRecordUsing(function ($record) {
                return $record->display_name;
            })
            ->indicateUsing(function (array $data): ?string {
                if (empty($data)) {
                    return null;
                }
                $authors = Author::whereIn('id', $data['values'])->get()->pluck('display_name');

                if (count($authors) === 0) {
                    return null;
                }

                return Str::plural('Author', count($authors)) .': ' . implode(', ', $authors->toArray());
            })
            ->relationship('authors', 'id')
            ->preload();
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
