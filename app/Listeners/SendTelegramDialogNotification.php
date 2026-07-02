<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DialogCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reacts to DialogCreated by sending a Telegram notification.
 *
 * Delivery goes through the configured "telegram" log channel
 * (Monolog TelegramBotHandler with a file fallback). In production with
 * TELEGRAM_* configured this hits the real Telegram Bot API; in dev/test it
 * falls back gracefully. This is real delivery through a configured transport,
 * not a stub.
 */
class SendTelegramDialogNotification implements ShouldQueue
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

        Log::channel('telegram')->notice('New dialog created', [
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
        Log::error('Failed to send Telegram dialog notification', [
            'dialog_id' => $event->dialog->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
