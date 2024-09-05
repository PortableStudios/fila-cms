<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages;
use Portable\FilaCms\Filament\Resources\TaxonomyResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Taxonomy;

class TaxonomyResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Taxonomies';

    // /**
    //  * @return array<NavigationItem>
    //  */
    public static function getNavigationItems(): array
    {
        $navItems = [];
        // Include default index
        $navItems[] =
            NavigationItem::make(static::getNavigationLabel())
            ->group(static::getNavigationGroup())
            ->parentItem(static::getNavigationParentItem())
            ->icon(static::getNavigationIcon())
            ->activeIcon(static::getActiveNavigationIcon())
            ->isActiveWhen(fn() => request()->routeIs(static::getRouteBaseName() . '.index'))
            ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
            ->badgeTooltip(static::getNavigationBadgeTooltip())
            ->sort(static::getNavigationSort())
            ->url(route(static::getRouteBaseName() . '.index'));

        // Include all taxonomies
        foreach (Taxonomy::all() as $taxonomy) {
            $navItems[] =
                NavigationItem::make($taxonomy->name)
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon('heroicon-o-square-2-stack')
                ->isActiveWhen(fn() => request()->routeIs(static::getRouteBaseName() . '.edit') && request()->route('record') == $taxonomy->id)
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->sort(static::getNavigationSort())
                ->url(route(static::getRouteBaseName() . '.edit', $taxonomy));
        }

        // Include direct link to create
        $navItems[] =
            NavigationItem::make('New' . ' ' . Str::singular(static::getNavigationLabel()))
            ->group(static::getNavigationGroup())
            ->parentItem(static::getNavigationParentItem())
            ->icon('heroicon-o-plus-circle')
            ->isActiveWhen(fn() => request()->routeIs(static::getRouteBaseName() . '.create'))
            ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
            ->badgeTooltip(static::getNavigationBadgeTooltip())
            ->sort(static::getNavigationSort())
            ->url(route(static::getRouteBaseName() . '.create'));

        return $navItems;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FilaCms::maxTextInput('code', 255)
                    ->disabledOn('edit')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->columnSpanFull(),
                FilaCms::maxTextInput('name', 255)
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->columnSpanFull(),
                CheckboxList::make('taxonomy_resources')
                    ->label('Applies To')
                    ->options(FilaCms::getContentModels())
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('terms')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => $state > 0 ? 'primary' : 'gray')
                    ->state(fn(Taxonomy $record): float => $record->terms->count()),
            ])
            ->reorderRecordsTriggerAction(
                fn(Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering'),
            )
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order', auth()->user()->can('manage taxonomies'));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TermsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxonomies::route('/'),
            'create' => Pages\CreateTaxonomy::route('/create'),
            'edit' => Pages\EditTaxonomy::route('/{record}/edit'),
            'revisions' => Pages\TaxonomyRevisions::route('/{record}/revisions'),
        ];
    }
}
