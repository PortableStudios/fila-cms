<?php

namespace Portable\FilaCms\Filament\Resources;

use Portable\FilaCms\Filament\Resources\PageResource\Pages;

class PageResource extends AbstractContentResource
{
    protected static ?string $recordTitleAttribute = 'title';

    public static function getFrontendRoutePrefix()
    {
        // So that pages just register as /{slug} instead of /pages/{slug}
        return '';
    }

    public static function registerIndexRoute()
    {
        // We don't want a "Pages" listing page
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'revisions' => Pages\PageRevisions::route('/{record}/revisions'),
        ];
    }
}
