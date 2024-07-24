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

    protected $lastBatch = '';

    public function mount(): void
    {
        $this->lastBatch = (static::getModel())::latestBatch();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('scan')
                ->label('Check Links')
                ->action(function () {
                    Notification::make()
                        ->title('Manual scan started')
                        ->success()
                        ->send();
                    $this->executeScan();
                })
        ];
    }

    protected function executeScan()
    {
        LinkChecker::dispatchSync();
    }

    public function getLastScan()
    {
        $batchBeforeThat = (new (static::getModel()))
            ->orderBy('id', 'DESC')->limit(1)
            ->where('batch_id', '!=', $this->lastBatch)
            ->first();

        if (is_null($batchBeforeThat)) {
            return 'N/A';
        }
        return $batchBeforeThat->created_at->diffForHumans();
    }

    public function lastScanStatus()
    {
        if (is_null($this->lastBatch)) {
            return 'No prior scans';
        }

        $unscanned = (new (static::getModel()))
            ->where('batch_id', $this->lastBatch)
            ->unscanned()->count();

        if ($unscanned > 0) {
            return 'Running';
        }
        return 'Completed';
    }
}
