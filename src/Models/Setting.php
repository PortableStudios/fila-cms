<?php

namespace Portable\FilaCms\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Portable\FilaCms\Models\Traits\IsTenanted;
use Portable\FilaCms\Observers\SettingObserver;
use Throwable;

#[ObservedBy(SettingObserver::class)]
class Setting extends Model
{
    use IsTenanted;

    protected $fillable = [
        'key',
        'value'
    ];


    public static function set($key, $value)
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->save();
        Cache::forget(static::getCacheKey($key));
        Cache::set(static::getCacheKey($key));
    }

    public static function get($key)
    {
        try {
            return Cache::rememberForever(
                static::getCacheKey($key),
                function () use ($key) {
                    return self::where('key', $key)->first()?->value;
                }
            );
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }

    // Attibutes
    public function cacheKey(): Attribute
    {
        return Attribute::make(function () {
            return static::getCacheKey($this->key);
        });
    }

    public static function getCacheKey($key)
    {
        if (!config('fila-cms.multitenancy') || !auth()->check()) {
            return 'setting-' . $key;
        }

        if (auth()->check()) {
            return 'setting-' . Filament::getTenant()->getKey() . '-' . $key;
        }
    }
}
