<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DialogCreated;
use App\Mail\DialogCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Reacts to DialogCreated by sending a confirmation email to the dialog owner.
 *
 * The Mailable itself is queued; with MAIL_MAILER=log the message is really
 * rendered and written by the log mailer (a real transport), not faked.
 */
class SendDialogCreatedEmail implements ShouldQueue
{
    /** Name of the queue this listener is pushed onto. */
    public string $queue = 'notifications';

    /** Number of attempts before the job is marked as failed. */
    public int $tries = 3;

    /** Seconds to wait between retries (progressive backoff). */
    public array $backoff = [10, 30, 60];

    public function handle(DialogCreated $event): void
    {
        Mail::to($event->dialog->user->email)
            ->send(new DialogCreatedMail($event->dialog));
    }

    /**
     * Called when all retries have been exhausted.
     */
    public function failed(DialogCreated $event, Throwable $exception): void
    {
        Log::error('Failed to send dialog created email', [
            'dialog_id' => $event->dialog->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
