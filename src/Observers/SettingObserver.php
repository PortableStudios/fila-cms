<?php

namespace Portable\FilaCms\Observers;

use Illuminate\Support\Facades\Cache;
use Portable\FilaCms\Models\Setting;

class SettingObserver
{
    public function created(Setting $setting): void
    {
        $this->forgetSetting($setting->cacheKey);
    }

    public function updated(Setting $setting): void
    {
        $this->forgetSetting($setting->cacheKey);
    }

    public function deleted(Setting $setting): void
    {
        $this->forgetSetting($setting->cacheKey);
    }

    public function restored(Setting $setting): void
    {
        $this->forgetSetting($setting->cacheKey);
    }

    public function forceDeleted(Setting $setting): void
    {
        $this->forgetSetting($setting->cacheKey);
    }

    protected function forgetSetting(string $key): void
    {
        if (Cache::has($key)) {
            Cache::forget($key);
        }
    }
}
