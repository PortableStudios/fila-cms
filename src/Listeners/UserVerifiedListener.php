<?php

namespace Portable\FilaCms\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Notification;
use Portable\FilaCms\Notifications\WelcomeNotification;

class UserVerifiedListener
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        Notification::send($user, new WelcomeNotification($user));
    }
}
