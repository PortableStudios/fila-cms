<?php

namespace Portable\FilaCms\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class ReindexSearch implements ShouldQueue
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
    ) {
    }

    public function handle(): void
    {
        Artisan::call('fila-cms:sync-search');
        Notification::make()
                                ->title('Search index updated')
                                ->success()
                                ->sendToDatabase($this->user);
    }
}
