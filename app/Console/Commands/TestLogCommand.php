<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sends a test log record through the current default log channel.
 *
 * Useful for manually verifying Telegram delivery once a real bot token and
 * chat id are configured in .env:  php artisan log:test error
 */
class TestLogCommand extends Command
{
    protected $signature = 'log:test {level=error : Log level to emit (debug, info, warning, error, critical)}';

    protected $description = 'Emit a test log message through the default log channel (use to verify Telegram delivery)';

    public function handle(): int
    {
        /** @var string $level */
        $level = $this->argument('level');

        $message = sprintf(
            '[log:test] Test %s message from %s at %s',
            strtoupper($level),
            config('app.name'),
            now()->toDateTimeString(),
        );

        Log::log($level, $message, ['source' => 'log:test command']);

        $this->info("Dispatched a [{$level}] log record through the default channel.");

        return self::SUCCESS;
    }
}
