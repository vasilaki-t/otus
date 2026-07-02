<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    /**
     * @return list<Event>
     */
    private function events(): array
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make(Schedule::class);

        return $schedule->events();
    }

    private function findEvent(string $command): ?Event
    {
        foreach ($this->events() as $event) {
            if (str_contains((string) $event->command, $command)) {
                return $event;
            }
        }

        return null;
    }

    public function test_cache_warm_is_scheduled_hourly_on_one_server(): void
    {
        $event = $this->findEvent('cache:warm');

        $this->assertNotNull($event, 'cache:warm must be registered in the schedule');
        $this->assertSame('0 * * * *', $event->expression, 'cache:warm should run hourly');
        $this->assertTrue($event->onOneServer, 'cache:warm must use onOneServer for cluster safety');
    }

    public function test_cache_flush_stats_is_scheduled_daily_on_one_server(): void
    {
        $event = $this->findEvent('cache:flush-stats');

        $this->assertNotNull($event, 'cache:flush-stats must be registered in the schedule');
        $this->assertSame('0 0 * * *', $event->expression, 'daily flush should use the daily cron expression');
        $this->assertTrue($event->onOneServer, 'daily flush must use onOneServer for cluster safety');
    }

    public function test_cache_refresh_is_scheduled(): void
    {
        $event = $this->findEvent('cache:refresh');

        $this->assertNotNull($event, 'cache:refresh must be registered in the schedule');
        $this->assertSame('0 3 * * *', $event->expression, 'cache:refresh should run daily at 03:00');
        $this->assertTrue($event->onOneServer);
    }
}
