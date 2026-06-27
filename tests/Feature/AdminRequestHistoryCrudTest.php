<?php

namespace Tests\Feature;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\RequestHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRequestHistoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.request-histories.create'))
            ->assertOk();
    }

    public function test_admin_can_create_a_request_history(): void
    {
        $dialog = Dialog::factory()->create();
        $messenger = Messenger::factory()->create();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.request-histories.store'), [
                'dialog_id' => $dialog->id,
                'messenger_id' => $messenger->id,
                'request_text' => 'Hello there',
                'response_text' => 'General Kenobi',
            ])
            ->assertRedirect(route('admin.request-histories.index'));

        $this->assertDatabaseHas('request_histories', [
            'dialog_id' => $dialog->id,
            'request_text' => 'Hello there',
        ]);
    }

    public function test_admin_can_update_a_request_history(): void
    {
        $history = RequestHistory::factory()->create();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.request-histories.update', $history), [
                'dialog_id' => $history->dialog_id,
                'messenger_id' => $history->messenger_id,
                'request_text' => 'Updated text',
                'response_text' => null,
            ])
            ->assertRedirect(route('admin.request-histories.index'));

        $this->assertDatabaseHas('request_histories', [
            'id' => $history->id,
            'request_text' => 'Updated text',
        ]);
    }

    public function test_admin_can_delete_a_request_history(): void
    {
        $history = RequestHistory::factory()->create();

        $this->actingAs($this->createAdmin())
            ->delete(route('admin.request-histories.destroy', $history))
            ->assertRedirect(route('admin.request-histories.index'));

        $this->assertDatabaseMissing('request_histories', ['id' => $history->id]);
    }

    public function test_create_requires_valid_relations(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.request-histories.store'), [
                'dialog_id' => 999,
                'messenger_id' => 999,
                'request_text' => '',
            ])
            ->assertSessionHasErrors(['dialog_id', 'messenger_id', 'request_text']);
    }
}
