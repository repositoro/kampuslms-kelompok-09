<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // N+1 detection: error di lokal, silent di produksi
        Model::preventLazyLoading(! app()->isProduction());

        // Rate limiter untuk seluruh API terautentikasi
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => response()->json([
                    'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                ], 429));
        });

        // Rate limiter khusus endpoint login (mencegah brute force)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . '|' . $request->ip())
                ->response(fn () => response()->json([
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi setelah 1 menit.',
                ], 429));
        });
    }
}
