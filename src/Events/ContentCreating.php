<?php
 
namespace Portable\FilaCms\Events;
 
use Portable\FilaCms\Models\Page;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Str;

class ContentCreating
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
 
    /**
     * Create a new event instance.
     */
    public function __construct( public Page $page ) {
        $page->created_user_id = auth()->user()->id;
        $page->updated_user_id = auth()->user()->id;
        $page->slug = Str::slug($page->title);
    }
}