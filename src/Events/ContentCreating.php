<?php

namespace Portable\FilaCms\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Models\AbstractContentModel;

class ContentCreating
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public AbstractContentModel $page)
    {
        $page->created_user_id = auth()->user() ? auth()->user()->id : FilaCms::systemUser()->id;
        $page->updated_user_id = auth()->user() ? auth()->user()->id : FilaCms::systemUser()->id;

        if (!$page->is_draft && is_null($page->publish_at)) {
            $page->publish_at = Carbon::now()->subMinute();
        }
    }
}
