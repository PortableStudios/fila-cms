<?php

namespace Portable\FilaCms\Filament\Resources\LinkCheckResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Portable\FilaCms\Filament\Resources\LinkCheckResource;
use Portable\FilaCms\Jobs\LinkChecker;
use Filament\Notifications\Notification;

class ListLinkChecks extends ListRecords
{
    protected static string $resource = LinkCheckResource::class;
    protected static ?string $title = 'Broken Links';
    protected static string $view = 'fila-cms::admin.pages.broken-links';


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('scan')
                ->label('Check Links')
                ->action(function () {
                    $this->executeScan();
                })
        ];
    }

    protected function executeScan()
    {
        LinkChecker::dispatch();

        Notification::make()
            ->title('Links has been successfully rechecked')
            ->success()
            ->send();
    }

    public function getLastScan()
    {
        $lastBatch = (new (static::getModel()))->latestBatch();
        $batchBeforeThat = (new (static::getModel()))
            ->orderBy('id', 'DESC')->limit(1)
            ->whereNot('batch_id', $lastBatch)
            ->first();

        if (is_null($batchBeforeThat)) {
            return 'N/A';
        }
        return $batchBeforeThat->created_at->diffForHumans();
    }

    public function lastScanStatus()
    {
        $lastBatch = (new (static::getModel()))->latestBatch();

        if (is_null($lastBatch)) {
            return 'No prior scans';
        }

        $unscanned = (new (static::getModel()))->unscanned()->count();

        if ($unscanned > 0) {
            return 'Running';
        }
        return 'Completed';
    }
}
