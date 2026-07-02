<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ensure the role with the given slug exists and return it.
     */
    protected function role(string $slug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => ucfirst($slug)],
        );
    }

    /**
     * Create a regular user with the "user" role attached.
     */
    protected function createUser(): User
    {
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$this->role(Role::USER)->id]);

        return $user;
    }

    /**
     * Create a user with the "admin" role attached.
     */
    protected function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }
}
