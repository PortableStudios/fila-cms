<?php

namespace Portable\FilaCms\Jobs;

use Filament\Notifications\Notification;
use GuzzleHttp\TransferStats;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Portable\FilaCms\Models\LinkCheck;

class CheckLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LinkCheck $linkCheck,
    ) {
    }

    public function handle(): void
    {
        $timeOut = 0;

        try {
            $response = Http::timeout(10)
            ->withOptions([
                'on_stats' => function (TransferStats $stats) use (&$timeOut) {
                    $timeOut = $stats->getTransferTime();
                }
            ])
            ->head($this->linkCheck->url);

            $this->linkCheck->status_code = $response->status();
            $this->linkCheck->status_text = $response->reason();
            $this->linkCheck->timeout = $timeOut;
            $this->linkCheck->save();

        } catch (\Illuminate\Http\Client\ConnectionException $th) {
            $this->linkCheck->status_code = 404;
            $this->linkCheck->status_text = 'Not Found';
            $this->linkCheck->timeout = 0;
            $this->linkCheck->save();
        }

        // Was this the last check in the batch?  If so, send a notification
        if ($this->linkCheck->batchStatus($this->linkCheck->bastch_id) === 100) {
            Notification::make()
                ->title('Links has been successfully rechecked')
                ->success()
                ->send();
        }
    }
}
