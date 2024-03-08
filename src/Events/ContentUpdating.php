<?php

namespace Portable\FilaCms\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Portable\FilaCms\Models\AbstractContentModel;

class ContentUpdating
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public AbstractContentModel $page)
    {
        $page->updated_user_id = auth()->user() ? auth()->user()->id : $page->created_user_id;
        if(!$page->is_draft && is_null($page->publish_at)) {
            $page->publish_at = Carbon::now()->subMinute();
        }

    }
}
