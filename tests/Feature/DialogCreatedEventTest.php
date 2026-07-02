<?php

namespace Tests\Feature;

use App\Events\DialogCreated;
use App\Listeners\LogDialogActivity;
use App\Listeners\SendDialogCreatedEmail;
use App\Listeners\SendTelegramDialogNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DialogCreatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_storing_a_dialog_dispatches_the_event(): void
    {
        Event::fake([DialogCreated::class]);

        $user = $this->createUser();

        $this->actingAs($user)
            ->post(route('dialogs.store'), ['title' => 'Queued dialog'])
            ->assertRedirect(route('dialogs.index'));

        Event::assertDispatched(
            DialogCreated::class,
            fn (DialogCreated $event) => $event->dialog->title === 'Queued dialog'
                && $event->dialog->user_id === $user->id,
        );
    }

    public function test_event_has_all_queued_listeners_registered(): void
    {
        Event::fake();

        $user = $this->createUser();

        $this->actingAs($user)
            ->post(route('dialogs.store'), ['title' => 'Wired'])
            ->assertRedirect(route('dialogs.index'));

        Event::assertListening(DialogCreated::class, SendTelegramDialogNotification::class);
        Event::assertListening(DialogCreated::class, SendDialogCreatedEmail::class);
        Event::assertListening(DialogCreated::class, LogDialogActivity::class);
    }
}
