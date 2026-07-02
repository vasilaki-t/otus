<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StatisticsCache;
use Illuminate\Console\Command;

/**
 * Atomically refreshes the logic-level statistics cache: flush followed by an
 * eager warm-up so consumers never observe an empty cache window.
 */
class CacheRefreshCommand extends Command
{
    protected $signature = 'cache:refresh';

    protected $description = 'Refresh the statistics cache (flush + warm in one step)';

    public function handle(StatisticsCache $cache): int
    {
        $cache->warm();
        $this->info('Statistics cache refreshed (flushed and warmed).');

        return self::SUCCESS;
    }
}
