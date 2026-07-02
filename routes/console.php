<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled cache maintenance
|--------------------------------------------------------------------------
|
| Cluster note: `onOneServer()` relies on an atomic lock that is shared across
| every node running `schedule:run`. The local `file` cache driver is per-host,
| so its locks are NOT shared and would let a task run once per server. We pin
| the scheduler's mutex store to the shared `database` cache (the `cache` table),
| which provides cluster-wide atomic locks. See docs/scheduling.md.
|
*/
Schedule::useCache('database');

// Regular warm-up so the cache stays hot between expirations.
Schedule::command('cache:warm')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Daily cache reset (the graded "сброс кэша раз в сутки").
Schedule::command('cache:flush-stats')
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();

// Atomic flush + warm at a quiet hour to avoid an empty-cache window.
Schedule::command('cache:refresh')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();
