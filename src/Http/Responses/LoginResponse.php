<?php

namespace Portable\FilaCms\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $home = Auth::user()->hasRole('Admin') ? route('filament.admin.pages.dashboard') : '/';

        return redirect()->intended($home);
    }
}
