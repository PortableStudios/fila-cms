<?php

namespace Portable\FilaCms\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNoImplements
{
    use HasFactory;

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
}
