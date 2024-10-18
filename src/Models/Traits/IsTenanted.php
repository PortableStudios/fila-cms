<?php

namespace Portable\FilaCms\Models\Traits;

use Filament\Facades\Filament;

use Illuminate\Contracts\Database\Eloquent\Builder;

trait IsTenanted
{
    public static function bootIsTenanted()
    {
        if (!config('fila-cms.multitenancy')) {
            return;
        }
        static::addGlobalScope('team', function (Builder $builder) {
            if (auth()->check()) {
                $tenantField = config('permission.column_names.team_foreign_key');
                $builder->where($tenantField, Filament::getTenant()->getKey());
            }
        });

        static::creating(function ($model) {
            $tenantField = config('permission.column_names.team_foreign_key');
            $model->{$tenantField} = Filament::getTenant()->getKey();
        });
    }



}
