<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Feature
{
    protected static array $cache = [];

    public static function all(): array
    {
        return Config::get('features.modules', []);
    }

    public static function enabled(string $key): bool
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $default = (bool) Arr::get(self::all(), "$key.default", false);

        $value = Cache::rememberForever("feature:{$key}", function () use ($key, $default) {
            if (! Schema::hasTable('feature_flags')) {
                return $default;
            }

            $stored = DB::table('feature_flags')
                ->where('key', $key)
                ->value('enabled');

            return $stored === null ? $default : (bool) $stored;
        });

        return self::$cache[$key] = $value;
    }

    public static function set(string $key, bool $enabled): void
    {
        if (! Schema::hasTable('feature_flags')) {
            self::$cache[$key] = $enabled;
            return;
        }

        DB::table('feature_flags')->updateOrInsert(
            ['key' => $key],
            ['enabled' => $enabled, 'updated_at' => now(), 'created_at' => now()],
        );

        Cache::forget("feature:{$key}");
        self::$cache[$key] = $enabled;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
        foreach (array_keys(self::all()) as $key) {
            Cache::forget("feature:{$key}");
        }
    }
}
