<?php

namespace Tests\Feature\Locations;

use App\Models\Location;
use Tests\Feature\ApiTestCase;

class LocationTest extends ApiTestCase
{
    public function test_list_is_paginated_and_searchable(): void
    {
        $this->actingAsAdmin();
        Location::factory()->create(['name' => 'Findme Item']);
        Location::factory()->count(3)->create();

        $this->getJson('/api/locations?search=Findme')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_create_show_update_delete(): void
    {
        $this->actingAsAdmin();

        $id = $this->postJson('/api/locations', ['code' => 'qc1', 'name' => 'First', 'type' => 'store', 'address' => 'Hello'])
            ->assertCreated()->assertJsonPath('data.name', 'First')->assertJsonPath('data.code', 'QC1')->json('data.id');
        $this->getJson("/api/locations/{$id}")->assertOk()->assertJsonPath('data.address', 'Hello');
        $this->putJson("/api/locations/{$id}", ['code' => 'QC1', 'name' => 'Renamed', 'type' => 'warehouse', 'active' => false])
            ->assertOk()->assertJsonPath('data.active', false);
        $this->deleteJson("/api/locations/{$id}")->assertOk();
        $this->assertDatabaseMissing('locations', ['id' => $id]);
    }

    public function test_validation_and_permissions(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/api/locations', [])->assertStatus(422)->assertJsonValidationErrors(['code', 'name', 'type']);
        $this->postJson('/api/locations', ['code' => 'X', 'name' => 'X', 'type' => 'kiosk'])->assertStatus(422)->assertJsonValidationErrors('type');

        $this->actingAsUserWith(['location-list']);
        $this->getJson('/api/locations')->assertOk();
        $this->postJson('/api/locations', ['name' => 'Nope'])->assertStatus(403);
    }

    public function test_changes_are_audited(): void
    {
        $this->actingAsAdmin();
        $id = $this->postJson('/api/locations', ['code' => 'AUD', 'name' => 'Audited', 'type' => 'store'])->json('data.id');

        $this->getJson("/api/audit-trail?record=Location&record_id={$id}")->assertOk()->assertJsonPath('meta.total', 1);
    }
}
