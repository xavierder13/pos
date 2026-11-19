<?php

namespace Tests\Feature\Administration;

use App\Models\User;
use App\Support\Rbac;
use Spatie\Permission\Models\Role;
use Tests\Feature\ApiTestCase;

class UserTest extends ApiTestCase
{
    public function test_list_is_paginated_and_searchable(): void
    {
        $this->actingAsAdmin();
        User::factory()->create(['name' => 'Jane Findme']);
        User::factory()->count(3)->create();

        $this->getJson('/api/users?search=Findme')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Jane Findme');
    }

    public function test_missing_permission_is_403(): void
    {
        $this->actingAsUserWith(['role-list']);

        $this->getJson('/api/users')->assertStatus(403);
    }

    public function test_create_update_and_delete(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Clerk', 'guard_name' => Rbac::GUARD]);

        $id = $this->postJson('/api/users', [
            'name' => 'New User', 'email' => 'new@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'roles' => [$role->id],
        ])->assertCreated()->assertJsonPath('data.roles.0.name', 'Clerk')->json('data.id');

        $this->putJson("/api/users/{$id}", [
            'name' => 'Renamed', 'email' => 'new@example.com', 'active' => false, 'roles' => [],
        ])->assertOk()->assertJsonPath('data.name', 'Renamed')->assertJsonPath('data.active', false);

        $this->deleteJson("/api/users/{$id}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $id]);
    }

    public function test_validation_errors_are_422_by_field(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/users', ['roles' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_cannot_delete_yourself_or_remove_the_last_administrator(): void
    {
        $admin = $this->actingAsAdmin();

        $this->deleteJson("/api/users/{$admin->id}")
            ->assertStatus(422)->assertJsonPath('message', 'You cannot delete your own account.');

        $this->actingAsUserWith(['user-edit']);
        $this->putJson("/api/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'active' => true, 'roles' => [],
        ])->assertStatus(422);
    }

    public function test_unknown_user_is_404(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/users/999')->assertNotFound()->assertJsonPath('message', 'Record not found.');
    }

    public function test_export_is_a_csv_of_the_filtered_list(): void
    {
        $this->actingAsAdmin();
        User::factory()->create(['name' => 'Zed Export', 'email' => 'zed@example.com']);

        $response = $this->get('/api/users/export?search=Zed', ['Accept' => 'application/json'])->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Name,Email,Roles,Status', $csv);
        $this->assertStringContainsString('zed@example.com', $csv);
        $this->assertSame(2, substr_count(trim($csv), "\n") + 1, 'header + one user');
    }

    public function test_one_users_history(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();
        $user->update(['name' => 'Renamed Person']);
        User::factory()->create()->update(['name' => 'Someone Else']);

        $this->getJson("/api/audit-trail?record=User&record_id={$user->id}")
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.changes.0.new', 'Renamed Person');
    }
}
