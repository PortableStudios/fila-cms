<?php

namespace Portable\FilaCms\Filament\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role;

trait HasContentRoles
{
    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'roleable', 'content_roles');
    }
}
