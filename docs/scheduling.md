# Task Scheduling — cache maintenance in a cluster

This document describes how the cache-maintenance jobs are scheduled and how to
run them safely on **N application servers** behind a load balancer.

## What is scheduled

The schedule lives in [`routes/console.php`](../routes/console.php) (Laravel
11+/13 style, using the `Illuminate\Support\Facades\Schedule` facade):

| Command             | Cron expression | Frequency      | Purpose                                  |
| ------------------- | --------------- | -------------- | ---------------------------------------- |
| `cache:warm`        | `0 * * * *`     | hourly         | Keep the statistics cache hot            |
| `cache:flush-stats` | `0 0 * * *`     | daily          | **Daily cache reset** (drop stale data)  |
| `cache:refresh`     | `0 3 * * *`     | daily at 03:00 | Atomic flush + warm (no empty window)    |

All three are registered with:

- `withoutOverlapping()` — a new run is skipped while a previous one is still
  in flight (protects against long-running overlaps).
- `onOneServer()` — in a cluster the job is executed by **exactly one** node per
  schedule tick (see below).
- `cache:warm` additionally uses `runInBackground()` so it does not block the
  scheduler tick.

## Running on every server

Add the **same** cron entry to **every** application server in the cluster. The
crontab line is identical on all nodes:

```cron
* * * * * cd /path/to/otus && php artisan schedule:run >> /dev/null 2>&1
```

Laravel's `schedule:run` wakes up every minute on each node and decides which
due jobs to dispatch. By itself that would mean every node runs every due job —
which is where `onOneServer()` comes in.

## Why `onOneServer()` prevents double execution

`onOneServer()` acquires an **atomic cache lock** before a job runs. The first
node to grab the lock for a given job+minute wins and executes it; the other
nodes see the lock is taken and skip the job. For this to work across the
cluster, the lock must live in a store that **all nodes share**.

### Shared lock store requirement

The scheduler's mutex store is pinned explicitly in `routes/console.php`:

```php
Schedule::useCache('database');
```

This is deliberate. The default cache driver in this project is `file`
(`CACHE_STORE=file`), and **file locks are local to each server's filesystem** —
they are *not* shared, so `onOneServer()` would let the job run once **per
server**. The `database` cache store (the `cache` table, shared by all nodes via
the same database) provides cluster-wide atomic locks, so the job runs exactly
once.

Any store that implements `Illuminate\Contracts\Cache\LockProvider` **and** is
shared across nodes works here:

- `database` — used by this project (requires the `cache` table; already migrated).
- `redis` — recommended for higher throughput clusters.
- `memcached` / `dynamodb` — also supported.

> A purely local store (`file`, `array`) must **not** be used for the scheduler
> mutex in a multi-server setup.

## Verifying

```bash
# See the registered schedule, frequencies and flags.
php artisan schedule:list

# Run the daily reset manually.
php artisan cache:flush-stats

# Warm + flush in one atomic step.
php artisan cache:refresh

# Force the scheduler to evaluate due jobs once (as cron would).
php artisan schedule:run
```

`php artisan schedule:list` shows `cache:warm` (hourly), `cache:flush-stats`
(daily) and `cache:refresh` (03:00), each marked to run on one server.
