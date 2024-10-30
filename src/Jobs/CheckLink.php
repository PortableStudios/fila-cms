<?php

namespace Portable\FilaCms\Jobs;

use GuzzleHttp\TransferStats;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Portable\FilaCms\Models\LinkCheck;
use Illuminate\Support\Facades\Notification;
use Portable\FilaCms\Notifications\ScanCompleteNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        public Authenticatable $user,
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

            if ($response->status() == 403) {
                // check if cf-mitigated
                if ($response->getHeader('cf-mitigated') === 'challenge') {
                    $this->linkCheck->status_code = 200;
                }
            }

            $this->linkCheck->save();

        } catch (\Illuminate\Http\Client\ConnectionException | \GuzzleHttp\Exception\TransferException $th) {
            $this->linkCheck->status_code = 404;
            $this->linkCheck->status_text = 'Not Found';
            $this->linkCheck->timeout = 0;
            $this->linkCheck->save();
        }

        // Was this the last check in the batch?  If so, send a notification
        if ($this->linkCheck->batchComplete()) {
            Notification::send($this->user, new ScanCompleteNotification());
        }
    }
}
