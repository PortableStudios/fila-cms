<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Author extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'is_individual',
    ];

    protected $casts = [
        'is_individual' => 'boolean',
    ];

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['is_individual'] ? $attributes['first_name'] . ' ' . $attributes['last_name'] : $attributes['first_name']
        );
    }
}
