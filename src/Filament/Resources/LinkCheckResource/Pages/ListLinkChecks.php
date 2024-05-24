<?php

namespace Portable\FilaCms\Filament\Resources\LinkCheckResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Portable\FilaCms\Filament\Resources\LinkCheckResource;
use Illuminate\Support\Facades\Artisan;

class ListLinkChecks extends ListRecords
{
    protected static string $resource = LinkCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('scan')
                ->label('Check Links')
                ->action(fn () => $this->executeScan())
        ];
    }

    protected function executeScan()
    {
        Artisan::call('fila-cms:verify-links');
    }
}
