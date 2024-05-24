<?php

namespace Portable\FilaCms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if(!app()->runningUnitTests()) {
            if($user && method_exists($user, 'hasEnabledTwoFactorAuthentication') && !$user->hasEnabledTwoFactorAuthentication()) {
                if (!Str::contains(Route::current()->getName(), ['two-factor','user-settings']) && Route::current()->getName() !== 'filament.admin.auth.logout') {
                    return redirect()->route('filament.admin.pages.user-settings');
                }
            }
        }

        return $next($request);
    }
}
