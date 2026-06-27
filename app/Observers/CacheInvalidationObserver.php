<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\StatisticsCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Invalidates the logic-level statistics cache whenever an observed model is
 * created, updated or deleted. Registered against the models whose changes
 * affect the cached dashboard counters / published pages list.
 */
class CacheInvalidationObserver
{
    public function __construct(private readonly StatisticsCache $cache) {}

    public function saved(Model $model): void
    {
        $this->cache->flush();
    }

    public function deleted(Model $model): void
    {
        $this->cache->flush();
    }

    public function restored(Model $model): void
    {
        $this->cache->flush();
    }
}
