<?php
 
namespace Portable\FilaCms\Events;
 
use Portable\FilaCms\Models\Page;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
 
class ContentUpdating
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
 
    /**
     * Create a new event instance.
     */
    public function __construct( public Page $order ) {
        $order->updated_user_id = auth()->user()->id;
    }
}