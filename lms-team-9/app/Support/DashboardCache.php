<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class DashboardCache
{
    private const VERSION_KEY = 'dashboard.cache.version';

    public static function remember(string $key, Closure $callback): mixed
    {
        Cache::add(self::VERSION_KEY, 1);
        $version = Cache::get(self::VERSION_KEY, 1);

        return Cache::remember(
            "dashboard:v{$version}:{$key}",
            now()->addMinutes(5),
            $callback,
        );
    }

    public static function invalidate(): void
    {
        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
    }
}
