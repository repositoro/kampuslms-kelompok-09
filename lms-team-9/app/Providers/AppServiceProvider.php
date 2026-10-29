<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;

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
        Paginator::defaultView('vendor.pagination.simple');
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        // Milestone M3 (1-8): Registrasi Event & Listener Asinkron
        Event::listen(
            \App\Events\AssignmentPublished::class,
            \App\Listeners\SendAssignmentNotification::class
        );

        Event::listen(
            \App\Events\GradeRecorded::class,
            \App\Listeners\SendGradeNotification::class
        );

        Event::listen(
            \App\Events\SubmissionSubmitted::class,
            \App\Listeners\SendSubmissionNotification::class
        );

        // Milestone M3 (4): Bagikan unreadNotificationsCount ke layout
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unreadNotifications()->count());
            }
        });
    }
}
