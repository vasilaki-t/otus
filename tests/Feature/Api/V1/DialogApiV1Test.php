<?php

namespace Tests\Feature\Api\V1;

use App\Models\Dialog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DialogApiV1Test extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        return $user;
    }

    public function test_index_requires_a_passport_token(): void
    {
        Dialog::factory()->count(2)->create();

        $this->getJson('/api/v1/dialogs')->assertStatus(401);
    }

    public function test_index_returns_only_the_authenticated_users_dialogs(): void
    {
        $user = $this->actingAsUser();
        Dialog::factory()->count(3)->create(['user_id' => $user->id]);
        Dialog::factory()->count(2)->create(); // belong to other users

        $this->getJson('/api/v1/dialogs')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'title', 'user_id', 'created_at', 'updated_at']],
                'links',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_show_returns_an_owned_dialog(): void
    {
        $user = $this->actingAsUser();
        $dialog = Dialog::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/v1/dialogs/{$dialog->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $dialog->id)
            ->assertJsonPath('data.title', $dialog->title);
    }

    public function test_show_forbids_access_to_another_users_dialog(): void
    {
        $this->actingAsUser();
        $foreign = Dialog::factory()->create();

        $this->getJson("/api/v1/dialogs/{$foreign->id}")->assertForbidden();
    }

    public function test_show_missing_dialog_returns_404(): void
    {
        $this->actingAsUser();

        $this->getJson('/api/v1/dialogs/999999')->assertNotFound();
    }

    public function test_store_creates_a_dialog_for_the_authenticated_user(): void
    {
        $user = $this->actingAsUser();

        $this->postJson('/api/v1/dialogs', ['title' => 'External task'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'External task')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('message', 'Диалог создан.');

        $this->assertDatabaseHas('dialogs', [
            'title' => 'External task',
            'user_id' => $user->id,
        ]);
    }

    public function test_store_validates_the_payload(): void
    {
        $this->actingAsUser();

        $this->postJson('/api/v1/dialogs', ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_update_modifies_an_owned_dialog(): void
    {
        $user = $this->actingAsUser();
        $dialog = Dialog::factory()->create(['user_id' => $user->id]);

        $this->putJson("/api/v1/dialogs/{$dialog->id}", ['title' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Renamed');

        $this->assertDatabaseHas('dialogs', ['id' => $dialog->id, 'title' => 'Renamed']);
    }

    public function test_update_forbids_a_foreign_dialog(): void
    {
        $this->actingAsUser();
        $foreign = Dialog::factory()->create();

        $this->putJson("/api/v1/dialogs/{$foreign->id}", ['title' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_destroy_deletes_an_owned_dialog(): void
    {
        $user = $this->actingAsUser();
        $dialog = Dialog::factory()->create(['user_id' => $user->id]);

        $this->deleteJson("/api/v1/dialogs/{$dialog->id}")->assertNoContent();

        $this->assertDatabaseMissing('dialogs', ['id' => $dialog->id]);
    }

    public function test_destroy_forbids_a_foreign_dialog(): void
    {
        $this->actingAsUser();
        $foreign = Dialog::factory()->create();

        $this->deleteJson("/api/v1/dialogs/{$foreign->id}")->assertForbidden();

        $this->assertDatabaseHas('dialogs', ['id' => $foreign->id]);
    }
}
