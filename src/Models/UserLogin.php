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
        $userModel = new $userModelClass();

        return $this->belongsTo($userModel::class, 'user_id');
    }
}
