<?php

declare(strict_types=1);

use App\Services\StatisticsCache;

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time To Live (TTL)
    |--------------------------------------------------------------------------
    |
    | Lifetime (in seconds) of the business-logic cache entries managed by the
    | StatisticsCache service. After this period the entries expire and are
    | recomputed lazily on the next read (or eagerly via `cache:warm`).
    |
    */

    'ttl' => (int) env('CACHE_WARM_TTL', 600),

    /*
    |--------------------------------------------------------------------------
    | Warmable Cache Keys
    |--------------------------------------------------------------------------
    |
    | The list of logic-level cache keys produced by the StatisticsCache
    | service. Used by `cache:warm` for reporting and by the invalidation
    | logic (flush) as the authoritative key registry.
    |
    */

    'keys' => [
        StatisticsCache::KEY_DASHBOARD_COUNTS,
        StatisticsCache::KEY_PUBLISHED_PAGES,
    ],

];
