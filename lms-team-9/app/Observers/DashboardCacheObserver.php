<?php

namespace App\Observers;

use App\Support\DashboardCache;
use Illuminate\Database\Eloquent\Model;

class DashboardCacheObserver
{
    public function saved(Model $model): void
    {
        DashboardCache::invalidate();
    }

    public function deleted(Model $model): void
    {
        DashboardCache::invalidate();
    }

    public function restored(Model $model): void
    {
        DashboardCache::invalidate();
    }
}
