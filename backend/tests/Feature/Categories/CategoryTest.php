<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use Tests\Feature\ApiTestCase;

class CategoryTest extends ApiTestCase
{
    public function test_list_is_paginated_and_searchable(): void
    {
        $this->actingAsAdmin();
        Category::factory()->create(['name' => 'Findme Item']);
        Category::factory()->count(3)->create();

        $this->getJson('/api/categories?search=Findme')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_create_show_update_delete(): void
    {
        $this->actingAsAdmin();

        $id = $this->postJson('/api/categories', ['name' => 'First', 'description' => 'Hello'])
            ->assertCreated()->assertJsonPath('data.name', 'First')->json('data.id');
        $this->getJson("/api/categories/{$id}")->assertOk()->assertJsonPath('data.description', 'Hello');
        $this->putJson("/api/categories/{$id}", ['name' => 'Renamed', 'active' => false])
            ->assertOk()->assertJsonPath('data.active', false);
        $this->deleteJson("/api/categories/{$id}")->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $id]);
    }

    public function test_validation_and_permissions(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/api/categories', [])->assertStatus(422)->assertJsonValidationErrors('name');

        $this->actingAsUserWith(['category-list']);
        $this->getJson('/api/categories')->assertOk();
        $this->postJson('/api/categories', ['name' => 'Nope'])->assertStatus(403);
    }

    public function test_changes_are_audited(): void
    {
        $this->actingAsAdmin();
        $id = $this->postJson('/api/categories', ['name' => 'Audited'])->json('data.id');

        $this->getJson("/api/audit-trail?record=Category&record_id={$id}")->assertOk()->assertJsonPath('meta.total', 1);
    }
}
