<?php

namespace Portable\FilaCms\Filament\Pages;

use Filament\Pages\Page;

class MediaLibrary extends Page
{
    protected static string $view = 'fila-cms::pages.media-library';
    protected static ?string $navigationIcon = 'heroicon-s-camera';
    protected static ?string $navigationGroup = 'Content';

}
