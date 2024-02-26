<?php

namespace Portable\FilaCms\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Portable\FilaCms\Models\Page;

class ContentUpdating
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Page $order)
    {
        $order->updated_user_id = auth()->user() ? auth()->user()->id : $order->created_user_id;
    }
}
