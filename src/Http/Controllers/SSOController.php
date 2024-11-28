<?php

namespace Portable\FilaCms\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Socialite\Facades\Socialite;
use Portable\FilaCms\Models\UserSsoLink;

class SSOController extends Controller
{
    /**
     * Show the profile for a given user.
     */
    public function redirectToProvider()
    {
        $driver = preg_match("/login\/(.*)/", Route::current()->uri(), $matches) ? $matches[1] : null;

        $socialiteDriver = $driver;
        if (Str::lower($socialiteDriver) === 'linkedin') {
            $socialiteDriver = 'linkedin-openid';
        }

        config(['services.' . $socialiteDriver => [
            'client_id' => config('settings.sso.' . $driver . '.client_id'),
            'client_secret' => config('settings.sso.' . $driver . '.client_secret'),
            'redirect' => config('app.url') . '/login/' . $driver . '/callback',
        ]]);

        return Socialite::driver($socialiteDriver)->redirect();
    }

    public function handleProviderCallback(LoginResponse $loginResponse)
    {
        $driver = preg_match("/login\/(.*)\//", Route::current()->uri(), $matches) ? $matches[1] : null;

        $socialiteDriver = $driver;
        if (Str::lower($socialiteDriver) === 'linkedin') {
            $socialiteDriver = 'linkedin-openid';
        }

        config(['services.' . $socialiteDriver => [
            'client_id' => config('settings.sso.' . $driver . '.client_id'),
            'client_secret' => config('settings.sso.' . $driver . '.client_secret'),
            'redirect' => config('app.url') . '/login/' . $driver . '/callback',
        ]]);

        $userModel = config('auth.providers.users.model');
        $ssoUser = Socialite::driver($socialiteDriver)->user();
        $ssoLink = UserSsoLink::where('driver', $driver)->where('provider_id', $ssoUser->getId())->first();

        if (!$ssoLink) {
            // Do we already have a user with this email?
            $user = $userModel::withTrashed()->where('email', $ssoUser->getEmail())->first();
            if (!$user) {
                $user = $userModel::create([
                    'name' => $ssoUser->getName(),
                    'email' => $ssoUser->getEmail(),
                    'password' => Hash::make(Str::random(24)) // This field can't be null on the db, so we assign it something
                ]);
            }
            $ssoLink = UserSsoLink::create([
                'user_id' => $user->id,
                'driver' => $driver,
                'provider_id' => $ssoUser->getId(),
                'provider_token' => $ssoUser->token,
                'provider_refresh_token' => $ssoUser->refreshToken,
            ]);
        }

        if (!$ssoLink->user) {
            $user = $userModel::withTrashed()->find($ssoLink->user_id);
            $user->roles()->detach();
            $user->restore();
            $ssoLink->refresh();
        }

        Auth::login($ssoLink->user);

        return $loginResponse->toResponse(request());
    }
}
