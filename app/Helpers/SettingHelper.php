<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingHelper
{
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = DB::table('settings')->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function clear(string $key)
    {
        Cache::forget('setting_' . $key);
    }
}
