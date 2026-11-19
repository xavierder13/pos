<?php

namespace Tests\Feature\Administration;

use App\Models\Role;
use App\Models\User;
use App\Support\Rbac;
use Spatie\Permission\Models\Permission;
use Tests\Feature\ApiTestCase;

class RoleTest extends ApiTestCase
{
    public function test_create_and_update_a_role_with_permissions(): void
    {
        $this->actingAsAdmin();
        $list = Permission::findByName('user-list', Rbac::GUARD);

        $id = $this->postJson('/api/roles', ['name' => 'Viewer', 'permissions' => [$list->id]])
            ->assertCreated()->assertJsonPath('data.permissions.0.name', 'user-list')->json('data.id');

        $this->putJson("/api/roles/{$id}", ['name' => 'Viewer 2', 'permissions' => []])
            ->assertOk()->assertJsonPath('data.name', 'Viewer 2')->assertJsonPath('data.permissions', []);
    }

    // withCount('users') under a real bearer token (auth:sanctum switches the default guard)
    public function test_list_with_a_real_token(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);
        $user->assignRole(Rbac::ADMINISTRATOR);
        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret-password'])->json('access_token');

        $this->withToken($token)->getJson('/api/roles')
            ->assertOk()
            ->assertJsonPath('data.0.name', Rbac::ADMINISTRATOR)
            ->assertJsonPath('data.0.users_count', 1)
            ->assertJsonPath('data.0.read_only', true);
    }

    public function test_the_administrator_role_is_read_only(): void
    {
        $this->actingAsAdmin();
        $admin = Role::findByName(Rbac::ADMINISTRATOR, Rbac::GUARD);

        $this->putJson("/api/roles/{$admin->id}", ['name' => 'Renamed', 'permissions' => []])->assertStatus(403);
        $this->deleteJson("/api/roles/{$admin->id}")->assertStatus(403);
    }

    public function test_a_role_in_use_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'In use', 'guard_name' => Rbac::GUARD]);
        User::factory()->create()->assignRole($role);

        $this->deleteJson("/api/roles/{$role->id}")->assertStatus(422);
    }

    public function test_changes_are_in_the_audit_trail(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/api/roles', ['name' => 'Audited', 'permissions' => []])->assertCreated();

        $this->getJson('/api/audit-trail?record=Role')
            ->assertOk()
            ->assertJsonPath('data.0.action', 'created')
            ->assertJsonPath('data.0.changes.0.new', 'Audited');
    }

    public function test_permissions_list(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/permissions')->assertOk()->assertJsonFragment(['name' => 'user-create', 'module' => 'user']);
    }

    public function test_show_lists_the_roles_users(): void
    {
        $this->actingAsUserWith(['role-list']);
        $role = Role::create(['name' => 'Shown', 'guard_name' => Rbac::GUARD]);
        $user = User::factory()->create(['name' => 'Holder']);
        $user->assignRole($role);

        $this->getJson("/api/roles/{$role->id}")
            ->assertOk()
            ->assertJsonPath('data.users.0.name', 'Holder')
            ->assertJsonPath('data.users_count', 1);
    }
}
