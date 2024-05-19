<?php

namespace Portable\FilaCms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Portable\FilaCms\Facades\FilaCms;
use Symfony\Component\HttpFoundation\Response;

class ContentRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $contentModels = FilaCms::getRawContentModels();

        $path = $request->path();
        foreach ($contentModels as $modelClass => $resourceClass) {
            $prefix = $resourceClass::getFrontendRoutePrefix();

            if (Str::of($path)->startsWith($prefix . '/')) {
                $slug = Str::of($path)->replace($prefix . '/', '');
                $model = (new $modelClass())->where('slug', $slug)->first();

                if (is_null($model) === false) {
                    $roles = $model->roles()->get()->pluck(['name']);

                    if ($roles->count() === 0) {
                        break;
                    }

                    if ($roles->count() > 0 && $request->user() === null) {
                        abort(403);
                    }

                    $roleNames = $request->user()->getRoleNames();
                    $intersect = $roles->intersect($roleNames);

                    if ($intersect->count() === 0) {
                        abort(403);
                    }
                }
                break;
            }
        }

        return $next($request);
    }
}
