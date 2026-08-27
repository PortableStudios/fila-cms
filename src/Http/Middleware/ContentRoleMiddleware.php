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

            // A prefix-less resource serves content from the root, so its slug is the whole
            // path. request()->path() has no leading slash, so the old startsWith($prefix.'/')
            // check never matched those and their role restrictions went unenforced.
            if ($prefix === '') {
                if (Str::of($path)->contains('/')) {
                    continue;
                }
                $slug = $path;
            } elseif (Str::of($path)->startsWith($prefix . '/')) {
                $slug = (string) Str::of($path)->replace($prefix . '/', '');
            } else {
                continue;
            }

            $model = (new $modelClass())->where('slug', $slug)->first();

            // Another content type may own this path; keep looking rather than bailing out.
            if (is_null($model)) {
                continue;
            }

            $roles = $model->roles()->get()->pluck(['name']);

            if ($roles->count() === 0) {
                break;
            }

            if ($request->user() === null) {
                abort(403);
            }

            if ($roles->intersect($request->user()->getRoleNames())->count() === 0) {
                abort(403);
            }

            break;
        }

        return $next($request);
    }
}
