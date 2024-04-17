<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        $userModelClass = config('auth.providers.users.model');

        return $this->belongsTo($userModelClass, 'user_id');
    }
}
