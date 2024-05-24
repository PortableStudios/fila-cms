<?php

namespace Portable\FilaCms\Filament\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

trait IsProtectedResource
{
    public static function can(string $action, ?Model $record = null): bool
    {
        $response = null;
        $permName = static::getPluralModelLabel();
        // Do permissions exist for this model?
        $guard = Filament::auth();
        $user = $guard->user();
        if (! $user) {
            return false;
        }

        if (Permission::whereIn('name', ['view '.$permName, 'manage '.$permName])->count()) {
            if ($action == 'viewAny' || $action == 'view') {
                $response = $user->hasPermissionTo('view '.$permName);
                if ($response !== null) {
                    return (bool) $response;
                }
            }
            if ($action == 'create' || $action == 'update' || $action == 'delete') {
                $response = $user->hasPermissionTo('manage '.$permName);
                if ($response !== null) {
                    return (bool) $response;
                }
            }
        }

        return false;
    }
}
