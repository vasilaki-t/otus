<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DialogCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reacts to DialogCreated by recording the activity in the application log.
 */
class LogDialogActivity implements ShouldQueue
{
    /** Name of the queue this listener is pushed onto. */
    public string $queue = 'notifications';

    /** Number of attempts before the job is marked as failed. */
    public int $tries = 3;

    /** Seconds to wait between retries (progressive backoff). */
    public array $backoff = [10, 30, 60];

    public function handle(DialogCreated $event): void
    {
        $dialog = $event->dialog;

        Log::info('Dialog activity: dialog created', [
            'dialog_id' => $dialog->id,
            'title' => $dialog->title,
            'user_id' => $dialog->user_id,
        ]);
    }

    /**
     * Called when all retries have been exhausted.
     */
    public function failed(DialogCreated $event, Throwable $exception): void
    {
        Log::error('Failed to log dialog activity', [
            'dialog_id' => $event->dialog->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
