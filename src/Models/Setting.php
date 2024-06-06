<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Portable\FilaCms\Observers\SettingObserver;

#[ObservedBy(SettingObserver::class)]
class Setting extends Model
{
    protected string $cacheKey;

    protected $fillable = [
        'key',
        'value'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->cacheKey = 'setting-' . $this->key;
    }

    public static function get($key)
    {
        return Cache::rememberForever(
            'setting-' . $key,
            function () use ($key) {
                return self::where('key', $key)->first()?->value;
            }
        );
    }
}
