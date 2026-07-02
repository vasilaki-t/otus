<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_page(): void
    {
        $response = $this->actingAs($this->createAdmin())->post(route('admin.pages.store'), [
            'title' => 'About us',
            'slug' => 'about-us',
            'content' => 'Some content',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', [
            'slug' => 'about-us',
            'title' => 'About us',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_update_a_page(): void
    {
        $page = Page::factory()->create(['title' => 'Old title']);

        $response = $this->actingAs($this->createAdmin())->put(route('admin.pages.update', $page), [
            'title' => 'New title',
            'slug' => $page->slug,
            'content' => 'Updated',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'New title',
        ]);
    }

    public function test_admin_can_delete_a_page(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->createAdmin())
            ->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_toggle_switches_publication_state(): void
    {
        $page = Page::factory()->create([
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.pages.toggle', $page))
            ->assertRedirect(route('admin.pages.index'));

        $page->refresh();
        $this->assertTrue($page->is_published);
        $this->assertNotNull($page->published_at);
    }

    public function test_regular_user_cannot_create_a_page(): void
    {
        $this->actingAs($this->createUser())
            ->post(route('admin.pages.store'), [
                'title' => 'Hack',
                'slug' => 'hack',
                'is_published' => '0',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('pages', ['slug' => 'hack']);
    }
}
