<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class UserSsoLink extends Model
{
    protected $fillable = [
        'user_id',
        'driver',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
