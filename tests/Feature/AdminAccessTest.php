<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden(): void
    {
        $this->actingAs($this->createUser())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->createAdmin())
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_access_management_sections(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.messengers.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.request-histories.index'))->assertOk();
    }
}
