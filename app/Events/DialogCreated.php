<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Dialog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event published when a user starts a new dialog.
 *
 * The event itself is dispatched synchronously, but every listener implements
 * ShouldQueue, so the actual side effects (Telegram / email / log) are handled
 * by queued jobs that the queue worker picks up from the `jobs` table.
 */
class DialogCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Dialog $dialog) {}
}
