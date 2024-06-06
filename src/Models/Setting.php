<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Portable\FilaCms\Observers\SettingObserver;

#[ObservedBy(SettingObserver::class)]
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value'
    ];

    public static function get($key)
    {
        return Cache::rememberForever(
            'setting-' . $key,
            function () use ($key) {
                return self::where('key', $key)->first()?->value;
            }
        );
    }

    // Attibutes
    public function cacheKey(): Attribute
    {
        return Attribute::make(get: fn () => 'setting-' . $this->key);
    }
}
