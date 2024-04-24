<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value'
    ];

    public static function get($key)
    {
        return self::where('key', $key)->first()?->value;
    }
}
