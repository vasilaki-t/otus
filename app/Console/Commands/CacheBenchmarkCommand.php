<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\Page;
use App\Models\RequestHistory;
use App\Models\User;
use App\Services\StatisticsCache;
use Illuminate\Console\Command;

/**
 * Measures the performance impact of the logic-level cache by comparing direct
 * database aggregation against cached reads over N iterations.
 */
class CacheBenchmarkCommand extends Command
{
    protected $signature = 'cache:benchmark {--iterations=100 : Number of iterations per scenario}';

    protected $description = 'Benchmark dashboard statistics with and without the logic-level cache';

    public function handle(StatisticsCache $cache): int
    {
        $iterations = max(1, (int) $this->option('iterations'));

        // Scenario 1: no cache — recompute aggregates from the database every time.
        $cache->flush();
        $noCacheStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->directCounts();
        }
        $noCacheTotal = (microtime(true) - $noCacheStart) * 1000;

        // Scenario 2: with cache — first call is a miss, the rest are hits.
        $cache->flush();
        $cachedStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $cache->dashboardCounts();
        }
        $cachedTotal = (microtime(true) - $cachedStart) * 1000;

        $noCacheAvg = $noCacheTotal / $iterations;
        $cachedAvg = $cachedTotal / $iterations;
        $speedup = $cachedAvg > 0 ? $noCacheAvg / $cachedAvg : 0.0;

        $this->info(sprintf('Cache benchmark over %d iterations:', $iterations));
        $this->table(
            ['Scenario', 'Total (ms)', 'Avg (ms)'],
            [
                ['Without cache (direct DB)', number_format($noCacheTotal, 3), number_format($noCacheAvg, 4)],
                ['With cache (remember)', number_format($cachedTotal, 3), number_format($cachedAvg, 4)],
            ],
        );
        $this->info(sprintf('Speed-up: %sx faster with cache.', number_format($speedup, 1)));

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function directCounts(): array
    {
        return [
            'pages' => Page::query()->count(),
            'published_pages' => Page::query()->where('is_published', true)->count(),
            'messengers' => Messenger::query()->count(),
            'dialogs' => Dialog::query()->count(),
            'request_histories' => RequestHistory::query()->count(),
            'users' => User::query()->count(),
        ];
    }
}
