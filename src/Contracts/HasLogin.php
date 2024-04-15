<?php

namespace Portable\FilaCms\Contracts;

use Portable\FilaCms\Models\UserLogin;

trait HasLogin
{
    public function logins()
    {
        return $this->hasMany(UserLogin::class, 'user_id');
    }
}
