<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StatisticsCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates the logic-level statistics cache.
 *
 * Designed to be run once a day from the scheduler so stale aggregate data is
 * dropped and recomputed lazily (or eagerly via `cache:warm`). The optional
 * `--all` flag additionally flushes the entire cache store.
 */
class CacheFlushCommand extends Command
{
    protected $signature = 'cache:flush-stats {--all : Also flush the entire cache store, not only managed keys}';

    protected $description = 'Flush the managed statistics cache keys (daily cache reset)';

    public function handle(StatisticsCache $cache): int
    {
        $cache->flush();
        $this->info(sprintf('Flushed %d managed statistics cache key(s).', count($cache->keys())));

        if ($this->option('all')) {
            Cache::flush();
            $this->warn('Flushed the entire cache store (--all).');
        }

        return self::SUCCESS;
    }
}
