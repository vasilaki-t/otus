<?php

namespace Tests\Feature;

use App\Models\Dialog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DialogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_their_dialogs_list(): void
    {
        $user = $this->createUser();
        Dialog::factory()->create(['user_id' => $user->id, 'title' => 'My dialog']);

        $this->actingAs($user)
            ->get(route('dialogs.index'))
            ->assertOk()
            ->assertSee('My dialog');
    }

    public function test_user_can_create_a_dialog_owned_by_themselves(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('dialogs.store'), [
            'title' => 'Fresh dialog',
        ]);

        $response->assertRedirect(route('dialogs.index'));
        $this->assertDatabaseHas('dialogs', [
            'title' => 'Fresh dialog',
            'user_id' => $user->id,
        ]);
    }

    public function test_owner_can_update_their_dialog(): void
    {
        $user = $this->createUser();
        $dialog = Dialog::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('dialogs.update', $dialog), [
            'title' => 'Renamed',
        ]);

        $response->assertRedirect(route('dialogs.index'));
        $this->assertDatabaseHas('dialogs', ['id' => $dialog->id, 'title' => 'Renamed']);
    }

    public function test_user_cannot_update_foreign_dialog(): void
    {
        $owner = $this->createUser();
        $intruder = $this->createUser();
        $dialog = Dialog::factory()->create(['user_id' => $owner->id, 'title' => 'Private']);

        $this->actingAs($intruder)
            ->put(route('dialogs.update', $dialog), ['title' => 'Hacked'])
            ->assertForbidden();

        $this->assertDatabaseHas('dialogs', ['id' => $dialog->id, 'title' => 'Private']);
    }

    public function test_user_cannot_delete_foreign_dialog(): void
    {
        $owner = $this->createUser();
        $intruder = $this->createUser();
        $dialog = Dialog::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->delete(route('dialogs.destroy', $dialog))
            ->assertForbidden();

        $this->assertDatabaseHas('dialogs', ['id' => $dialog->id]);
    }

    public function test_admin_can_update_any_dialog(): void
    {
        $owner = $this->createUser();
        $admin = $this->createAdmin();
        $dialog = Dialog::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($admin)
            ->put(route('dialogs.update', $dialog), ['title' => 'By admin'])
            ->assertRedirect(route('dialogs.index'));

        $this->assertDatabaseHas('dialogs', ['id' => $dialog->id, 'title' => 'By admin']);
    }
}
