<?php

namespace Tests\Feature\Api\V1;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\RequestHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->roles()->syncWithoutDetaching([$this->role(Role::USER)->id]);
        Passport::actingAs($user, ['*']);

        return $user;
    }

    /**
     * Create messages (request histories) on a dialog reusing a single
     * messenger, to avoid exhausting the factory's unique name pool.
     */
    private function makeMessages(Dialog $dialog, int $count): void
    {
        $messenger = Messenger::factory()->create();

        RequestHistory::factory()->count($count)->create([
            'dialog_id' => $dialog->id,
            'messenger_id' => $messenger->id,
        ]);
    }

    // ---------------------------------------------------------------------
    // Authorization: every account endpoint is token-only (401 without token)
    // ---------------------------------------------------------------------

    /**
     * @return array<string, array{string, string}>
     */
    public static function guardedEndpoints(): array
    {
        return [
            'profile' => ['getJson', '/api/v1/account/profile'],
            'update profile' => ['putJson', '/api/v1/account/profile'],
            'change password' => ['putJson', '/api/v1/account/password'],
            'dialogs' => ['getJson', '/api/v1/account/dialogs'],
            'stats' => ['getJson', '/api/v1/account/stats'],
        ];
    }

    #[DataProvider('guardedEndpoints')]
    public function test_account_endpoints_require_a_passport_token(string $method, string $uri): void
    {
        $this->{$method}($uri)->assertStatus(401);
    }

    // ---------------------------------------------------------------------
    // Profile
    // ---------------------------------------------------------------------

    public function test_profile_returns_the_authenticated_user_data(): void
    {
        $user = $this->actingAsUser([
            'username' => 'alice',
            'email' => 'alice@example.com',
        ]);

        $dialogs = Dialog::factory()->count(2)->create(['user_id' => $user->id]);
        $this->makeMessages($dialogs->first(), 3);

        $this->getJson('/api/v1/account/profile')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'username', 'email', 'roles',
                    'last_activity_at', 'dialogs_count', 'messages_count', 'created_at',
                ],
            ])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.username', 'alice')
            ->assertJsonPath('data.email', 'alice@example.com')
            ->assertJsonPath('data.roles', [Role::USER])
            ->assertJsonPath('data.dialogs_count', 2)
            ->assertJsonPath('data.messages_count', 3);
    }

    public function test_profile_does_not_leak_another_users_data(): void
    {
        $me = $this->actingAsUser(['username' => 'me', 'email' => 'me@example.com']);
        $other = User::factory()->create(['username' => 'other', 'email' => 'other@example.com']);
        Dialog::factory()->count(4)->create(['user_id' => $other->id]);

        $this->getJson('/api/v1/account/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $me->id)
            ->assertJsonPath('data.email', 'me@example.com')
            // counters reflect only my (empty) data, never the other user's.
            ->assertJsonPath('data.dialogs_count', 0);
    }

    public function test_profile_does_not_expose_the_password_hash(): void
    {
        $this->actingAsUser();

        $this->getJson('/api/v1/account/profile')
            ->assertOk()
            ->assertJsonMissingPath('data.password');
    }

    // ---------------------------------------------------------------------
    // Update profile
    // ---------------------------------------------------------------------

    public function test_update_profile_changes_username_and_email(): void
    {
        $user = $this->actingAsUser(['username' => 'old', 'email' => 'old@example.com']);

        $this->putJson('/api/v1/account/profile', [
            'username' => 'newname',
            'email' => 'new@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('data.username', 'newname')
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('message', 'Профиль обновлён.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'newname',
            'email' => 'new@example.com',
        ]);
    }

    public function test_update_profile_allows_keeping_own_unchanged_email(): void
    {
        $user = $this->actingAsUser(['username' => 'keep', 'email' => 'keep@example.com']);

        // Same email as the current user must NOT trigger the unique rule.
        $this->putJson('/api/v1/account/profile', ['email' => 'keep@example.com'])
            ->assertOk()
            ->assertJsonPath('data.email', 'keep@example.com');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'keep@example.com']);
    }

    public function test_update_profile_rejects_an_email_taken_by_another_user(): void
    {
        $this->actingAsUser(['email' => 'mine@example.com']);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->putJson('/api/v1/account/profile', ['email' => 'taken@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_update_profile_rejects_a_username_taken_by_another_user(): void
    {
        $this->actingAsUser(['username' => 'mine']);
        User::factory()->create(['username' => 'busy']);

        $this->putJson('/api/v1/account/profile', ['username' => 'busy'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');
    }

    public function test_update_profile_rejects_an_invalid_email(): void
    {
        $this->actingAsUser();

        $this->putJson('/api/v1/account/profile', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_update_profile_supports_patch(): void
    {
        $user = $this->actingAsUser(['username' => 'patchme']);

        $this->patchJson('/api/v1/account/profile', ['username' => 'patched'])
            ->assertOk()
            ->assertJsonPath('data.username', 'patched');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'patched']);
    }

    // ---------------------------------------------------------------------
    // Change password
    // ---------------------------------------------------------------------

    public function test_change_password_succeeds_with_the_correct_current_password(): void
    {
        $user = $this->actingAsUser(['password' => Hash::make('current-secret')]);

        $this->putJson('/api/v1/account/password', [
            'current_password' => 'current-secret',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Пароль изменён.');

        $this->assertTrue(Hash::check('brand-new-secret', $user->refresh()->password));
    }

    public function test_change_password_fails_with_a_wrong_current_password(): void
    {
        $user = $this->actingAsUser(['password' => Hash::make('current-secret')]);

        $this->putJson('/api/v1/account/password', [
            'current_password' => 'wrong-secret',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('current-secret', $user->refresh()->password));
    }

    public function test_change_password_rejects_a_short_new_password(): void
    {
        $this->actingAsUser(['password' => Hash::make('current-secret')]);

        $this->putJson('/api/v1/account/password', [
            'current_password' => 'current-secret',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_change_password_rejects_an_unconfirmed_new_password(): void
    {
        $this->actingAsUser(['password' => Hash::make('current-secret')]);

        $this->putJson('/api/v1/account/password', [
            'current_password' => 'current-secret',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'different-secret',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_login_works_with_the_new_password_after_change(): void
    {
        $user = $this->actingAsUser(['password' => Hash::make('current-secret')]);

        $this->putJson('/api/v1/account/password', [
            'current_password' => 'current-secret',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])->assertOk();

        // The Sanctum login endpoint authenticates with the new password.
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'brand-new-secret',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'username', 'email']]);

        // The old password no longer works.
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'current-secret',
        ])->assertStatus(401);
    }

    // ---------------------------------------------------------------------
    // Dialogs — per-user isolation (authorization aspect)
    // ---------------------------------------------------------------------

    public function test_dialogs_returns_only_the_authenticated_users_dialogs(): void
    {
        $user = $this->actingAsUser();
        Dialog::factory()->count(3)->create(['user_id' => $user->id]);

        $other = User::factory()->create();
        Dialog::factory()->count(5)->create(['user_id' => $other->id]);

        $this->getJson('/api/v1/account/dialogs')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'title', 'user_id', 'created_at', 'updated_at']],
                'links',
                'meta',
            ])
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.user_id', $user->id);
    }

    public function test_dialogs_isolation_user_b_does_not_see_user_a_dialogs(): void
    {
        $userA = User::factory()->create();
        $dialogA = Dialog::factory()->create(['user_id' => $userA->id]);

        $userB = $this->actingAsUser();
        Dialog::factory()->create(['user_id' => $userB->id]);

        $response = $this->getJson('/api/v1/account/dialogs')->assertOk();

        $returnedIds = collect($response->json('data'))->pluck('id');
        $this->assertFalse($returnedIds->contains($dialogA->id));
        $returnedUserIds = collect($response->json('data'))->pluck('user_id')->unique();
        $this->assertEquals([$userB->id], $returnedUserIds->values()->all());
    }

    // ---------------------------------------------------------------------
    // Stats
    // ---------------------------------------------------------------------

    public function test_stats_returns_correct_counters_for_the_user(): void
    {
        $user = $this->actingAsUser();
        $dialogs = Dialog::factory()->count(2)->create(['user_id' => $user->id]);
        $this->makeMessages($dialogs->first(), 4);
        $this->makeMessages($dialogs->last(), 1);

        // Noise belonging to another user must not be counted.
        $other = User::factory()->create();
        $otherDialog = Dialog::factory()->create(['user_id' => $other->id]);
        $this->makeMessages($otherDialog, 7);

        $this->getJson('/api/v1/account/stats')
            ->assertOk()
            ->assertJsonStructure(['data' => ['dialogs_count', 'messages_count', 'last_activity_at']])
            ->assertJsonPath('data.dialogs_count', 2)
            ->assertJsonPath('data.messages_count', 5);
    }
}
