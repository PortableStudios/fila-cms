<?php

namespace Portable\FilaCms\Filament\Resources\AuthorResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Portable\FilaCms\Filament\Resources\AuthorResource;

class ListAuthors extends ListRecords
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}