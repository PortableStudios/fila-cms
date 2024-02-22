<?php

namespace Portable\FilaCms\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Portable\FilaCms\Models\Page;
use Str;

class ContentCreating
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Page $page)
    {
        $page->created_user_id = auth()->user()->id;
        $page->updated_user_id = auth()->user()->id;

        if ($page->slug === null) {
            $page->slug = Str::slug($page->title);
        }
    }
}
