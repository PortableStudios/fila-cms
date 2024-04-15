<?php

namespace Portable\FilaCms\Listeners;

use Illuminate\Auth\Events\Login;
use Portable\FilaCms\Models\UserLogin;

class AuthenticationListener
{
    public function handle(Login $event): void
    {
        UserLogin::create([
            'user_id' => $event->user->id,
        ]);
    }
}
