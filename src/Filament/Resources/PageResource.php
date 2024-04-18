<?php

namespace Portable\FilaCms\Filament\Resources;

use Portable\FilaCms\Filament\Resources\PageResource\Pages;

class PageResource extends AbstractContentResource
{
    protected static ?string $recordTitleAttribute = 'title';

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
