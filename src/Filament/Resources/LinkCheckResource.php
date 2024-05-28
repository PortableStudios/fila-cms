<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Portable\FilaCms\Filament\Resources\LinkCheckResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\LinkCheck;

class LinkCheckResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = LinkCheck::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Broken Links';

    protected static ?string $navigationBadgeTooltip = 'The number of broken links';

    public static function getNavigationBadge(): ?string
    {
        return self::getFailedCount();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = self::getFailedCount();

        return $count === 0 ? 'success' : 'warning';
    }

    protected static function getFailedCount()
    {
        $batch = (static::getModel())::latestBatch();
        $count = static::getModel()::failedCount($batch);

        return $count;
    }

    public static function getModel(): string
    {
        return LinkCheck::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->url(fn (LinkCheck $check): string => $check->edit_url)
                    ->openUrlInNewTab(),
                TextColumn::make('origin_resource')->sortable(),
                TextColumn::make('url')
                    ->sortable()
                    ->url(fn (LinkCheck $check): string => $check->url)
                    ->openUrlInNewTab(),
                TextColumn::make('status_text')->label('Status')->sortable(),
                TextColumn::make('status_code')->label('Code')->sortable(),
                TextColumn::make('created_at')->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $lastBatch = LinkCheck::latestBatch();

                $query->failed()->where('batch_id', $lastBatch);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLinkChecks::route('/'),
        ];
    }
}
