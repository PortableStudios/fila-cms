<?php

namespace Portable\FilaCms\Tests;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements \Filament\Models\Contracts\FilamentUser
{
    use \Lab404\Impersonate\Models\Impersonate;
    use \Laravel\Fortify\TwoFactorAuthenticatable;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Portable\FilaCms\Contracts\HasLogin;
    use \Spatie\Permission\Traits\HasRoles;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function canAccessFilament(): bool
    {
        // This is required on Front and Back end.  Add more specific controls with authenticate middleware.
        return true;
    }

    public function canAccessPanel($panel): bool
    {
        // This is required on Front and Back end.  Add more specific controls with authenticate middleware.
        return true;
    }


    public function canImpersonate()
    {
        return $this->can('impersonate users');
    }

}
