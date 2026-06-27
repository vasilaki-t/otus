<?php

namespace Tests\Feature;

use App\Events\DialogCreated;
use App\Listeners\LogDialogActivity;
use App\Listeners\SendDialogCreatedEmail;
use App\Listeners\SendTelegramDialogNotification;
use App\Mail\DialogCreatedMail;
use App\Models\Dialog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DialogListenersTest extends TestCase
{
    use RefreshDatabase;

    private function makeDialog(): Dialog
    {
        $user = $this->createUser();

        return Dialog::factory()->create([
            'user_id' => $user->id,
            'title' => 'Listener dialog',
        ]);
    }

    public function test_email_listener_sends_the_mailable_to_the_owner(): void
    {
        Mail::fake();

        $dialog = $this->makeDialog();
        $event = new DialogCreated($dialog);

        (new SendDialogCreatedEmail)->handle($event);

        Mail::assertQueued(
            DialogCreatedMail::class,
            fn (DialogCreatedMail $mail) => $mail->hasTo($dialog->user->email),
        );
    }

    public function test_telegram_listener_runs_without_errors(): void
    {
        $event = new DialogCreated($this->makeDialog());

        (new SendTelegramDialogNotification)->handle($event);

        $this->assertTrue(true);
    }

    public function test_log_listener_writes_an_activity_record(): void
    {
        Log::spy();

        $dialog = $this->makeDialog();

        (new LogDialogActivity)->handle(new DialogCreated($dialog));

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Dialog activity: dialog created', \Mockery::on(
                fn (array $context) => $context['dialog_id'] === $dialog->id,
            ));
    }

    public function test_all_listeners_are_queueable(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new SendTelegramDialogNotification);
        $this->assertInstanceOf(ShouldQueue::class, new SendDialogCreatedEmail);
        $this->assertInstanceOf(ShouldQueue::class, new LogDialogActivity);
    }
}
