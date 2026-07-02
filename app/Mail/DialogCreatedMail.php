<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Dialog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmation email rendered and sent when a dialog is created.
 *
 * Implements ShouldQueue so the message is built and dispatched on the queue.
 * With MAIL_MAILER=log the message is really rendered and written to the log
 * mailer (a real transport), not faked.
 */
class DialogCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Dialog $dialog) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новый диалог создан: '.$this->dialog->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.dialog-created',
            with: [
                'title' => $this->dialog->title,
                'username' => $this->dialog->user->username,
            ],
        );
    }
}
