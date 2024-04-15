<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class Author extends Model
{
    use Versionable;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'first_name',
        'last_name',
        'is_individual',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'is_individual',
    ];

    protected $casts = [
        'is_individual' => 'boolean',
    ];

    protected $appends = ['display_name'];

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['is_individual'] ? $attributes['first_name'] . ' ' . $attributes['last_name'] : $attributes['first_name']
        );
    }
}
