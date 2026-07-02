<?php

namespace Tests\Feature\Api;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PageApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    public function test_index_requires_authentication(): void
    {
        Page::factory()->count(2)->create();

        $this->getJson('/api/pages')->assertStatus(401);
    }

    public function test_index_returns_paginated_pages(): void
    {
        $this->authenticate();
        Page::factory()->count(3)->create();

        $this->getJson('/api/pages')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'title', 'slug', 'is_published']],
                'links',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_show_returns_single_page(): void
    {
        $this->authenticate();
        $page = Page::factory()->create();

        $this->getJson("/api/pages/{$page->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $page->id)
            ->assertJsonPath('data.slug', $page->slug);
    }

    public function test_show_missing_page_returns_404(): void
    {
        $this->authenticate();

        $this->getJson('/api/pages/999999')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_store_creates_page_and_returns_201(): void
    {
        $this->authenticate();

        $this->postJson('/api/pages', [
            'title' => 'About us',
            'content' => 'Some content',
            'is_published' => true,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'About us')
            ->assertJsonPath('data.slug', 'about-us')
            ->assertJsonPath('data.is_published', true);

        $this->assertDatabaseHas('pages', [
            'slug' => 'about-us',
            'title' => 'About us',
            'is_published' => true,
        ]);
    }

    public function test_store_validates_required_title(): void
    {
        $this->authenticate();

        $this->postJson('/api/pages', [
            'content' => 'No title here',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/pages', [
            'title' => 'Hack',
            'is_published' => false,
        ])->assertStatus(401);

        $this->assertDatabaseMissing('pages', ['title' => 'Hack']);
    }

    public function test_update_modifies_page(): void
    {
        $this->authenticate();
        $page = Page::factory()->create(['title' => 'Old title']);

        $this->putJson("/api/pages/{$page->id}", [
            'title' => 'New title',
            'slug' => $page->slug,
            'content' => 'Updated',
            'is_published' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'New title');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'New title',
        ]);
    }

    public function test_destroy_deletes_page_and_returns_204(): void
    {
        $this->authenticate();
        $page = Page::factory()->create();

        $this->deleteJson("/api/pages/{$page->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
