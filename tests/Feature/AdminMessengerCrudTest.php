<?php

namespace Tests\Feature;

use App\Models\Messenger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMessengerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_messenger(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.messengers.store'), ['name' => 'telegram'])
            ->assertRedirect(route('admin.messengers.index'));

        $this->assertDatabaseHas('messengers', ['name' => 'telegram']);
    }

    public function test_admin_can_update_a_messenger(): void
    {
        $messenger = Messenger::factory()->create(['name' => 'old-name']);

        $this->actingAs($this->createAdmin())
            ->put(route('admin.messengers.update', $messenger), ['name' => 'new-name'])
            ->assertRedirect(route('admin.messengers.index'));

        $this->assertDatabaseHas('messengers', ['id' => $messenger->id, 'name' => 'new-name']);
    }

    public function test_admin_can_delete_a_messenger(): void
    {
        $messenger = Messenger::factory()->create();

        $this->actingAs($this->createAdmin())
            ->delete(route('admin.messengers.destroy', $messenger))
            ->assertRedirect(route('admin.messengers.index'));

        $this->assertDatabaseMissing('messengers', ['id' => $messenger->id]);
    }

    public function test_messenger_name_must_be_unique(): void
    {
        Messenger::factory()->create(['name' => 'taken']);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.messengers.store'), ['name' => 'taken'])
            ->assertSessionHasErrors('name');
    }
}
