<?php

namespace Portable\FilaCms\Http\Middleware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Filament\Models\Contracts\FilamentUser;
use Filament\Facades\Filament;

class FilaCmsAuthenticate extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentPanel();

        abort_if(!$user->hasPermissionTo('access filacms-backend'), 403);
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
