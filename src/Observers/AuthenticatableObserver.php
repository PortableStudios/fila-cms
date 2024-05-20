<?php

namespace Portable\FilaCms\Observers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Portable\FilaCms\Notifications\WelcomeNotification;

class AuthenticatableObserver
{
    public function created(Authenticatable $user): void
    {
        if (
            ! Arr::where(
            config('fortify.features'),
            function ($value, $key) {
                return $value === Features::emailVerification();
            })
        ) {
            Notification::send($user, new WelcomeNotification($user));
        }
    }

    public function updated(Authenticatable $user): void
    {
        //
    }

    public function deleted(Authenticatable $user): void
    {
        //
    }

    public function restored(Authenticatable $user): void
    {
        //
    }

    public function forceDeleted(Authenticatable $user): void
    {
        //
    }
}
