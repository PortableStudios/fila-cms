<?php
 
namespace Portable\FilaCms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Portable\FilaCms\Models\LinkCheck;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\TransferStats;

class CheckLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    /**
     * Create a new job instance.
     */
    public function __construct(
        public LinkCheck $linkCheck,
    ) {}
 
    public function handle(): void
    {
        $timeOut = 0;

        $response = Http::withOptions([
            'on_stats' => function(TransferStats $stats) use (&$timeOut) {
                $timeOut = $stats->getTransferTime();
            }
        ])
        ->get($this->linkCheck->url);

        $this->linkCheck->status_code = $response->status();
        $this->linkCheck->timeout = $timeOut;
        $this->linkCheck->save();
    }
}