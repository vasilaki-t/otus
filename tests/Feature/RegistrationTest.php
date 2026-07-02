<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_is_accessible_for_guests(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_user_can_register_and_is_authenticated_with_user_role(): void
    {
        $this->role(Role::USER);

        $response = $this->post('/register', [
            'username' => 'newbie',
            'email' => 'newbie@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect(route('dialogs.index'));

        $this->assertDatabaseHas('users', ['email' => 'newbie@example.com']);

        $user = User::query()->where('email', 'newbie@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(Role::USER));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'username' => 'newbie',
            'email' => 'newbie@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'newbie@example.com']);
    }
}
