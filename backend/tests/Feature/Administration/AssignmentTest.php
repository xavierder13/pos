<?php

namespace Tests\Feature\Administration;

use App\Models\Role;
use App\Models\User;
use App\Support\Rbac;
use Spatie\Permission\Models\Permission;
use Tests\Feature\ApiTestCase;

// One role → many users, one permission → many roles.
class AssignmentTest extends ApiTestCase
{
    public function test_assign_a_role_to_many_users(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Clerk', 'guard_name' => Rbac::GUARD]);
        $other = Role::create(['name' => 'Other', 'guard_name' => Rbac::GUARD]);
        [$a, $b, $c] = User::factory()->count(3)->create();
        $a->assignRole($role, $other);

        $this->getJson("/api/roles/{$role->id}/users")
            ->assertOk()->assertJsonPath('data', [$a->id])->assertJsonCount(4, 'users');

        $this->putJson("/api/roles/{$role->id}/users", ['user_ids' => [$b->id, $c->id]])
            ->assertOk()->assertJsonPath('data.users_count', 2);

        $this->assertFalse($a->fresh()->hasRole('Clerk'));
        $this->assertTrue($a->fresh()->hasRole('Other'), 'their other roles stay');
        $this->assertTrue($b->fresh()->hasRole('Clerk'));

        // in the audit trail, on the user
        $this->getJson('/api/audit-trail?record=User')->assertOk()->assertJsonFragment(['field' => 'roles']);
    }

    public function test_the_administrator_role_keeps_an_active_user(): void
    {
        $this->actingAsAdmin();
        $admin = Role::findByName(Rbac::ADMINISTRATOR, Rbac::GUARD);

        $this->putJson("/api/roles/{$admin->id}/users", ['user_ids' => []])
            ->assertStatus(422)->assertJsonPath('message', 'At least one active user must keep the Administrator role.');
    }

    public function test_assign_a_permission_to_many_roles(): void
    {
        $this->actingAsAdmin();
        $permission = Permission::findByName('user-list', Rbac::GUARD);
        $one = Role::create(['name' => 'One', 'guard_name' => Rbac::GUARD]);
        $two = Role::create(['name' => 'Two', 'guard_name' => Rbac::GUARD]);
        $one->givePermissionTo($permission);

        $this->putJson("/api/permissions/{$permission->id}/roles", ['role_ids' => [$two->id]])
            ->assertOk()
            // the Administrator always keeps every permission
            ->assertJsonPath('data.roles.*.name', [Rbac::ADMINISTRATOR, 'Two']);

        $this->assertFalse($one->fresh()->hasPermissionTo('user-list'));
        $this->assertTrue($two->fresh()->hasPermissionTo('user-list'));
    }

    public function test_assigning_needs_role_edit(): void
    {
        $this->actingAsUserWith(['role-list', 'permission-list']);
        $role = Role::create(['name' => 'X', 'guard_name' => Rbac::GUARD]);
        $permission = Permission::findByName('user-list', Rbac::GUARD);

        $this->getJson("/api/roles/{$role->id}/users")->assertStatus(403);
        $this->putJson("/api/permissions/{$permission->id}/roles", ['role_ids' => []])->assertStatus(403);
        $this->getJson('/api/permissions')->assertOk()->assertJsonStructure(['data' => [['id', 'name', 'module', 'roles' => [['id', 'name', 'users_count']]]]]);
    }

    public function test_users_list_carries_each_roles_permissions(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/users')->assertOk()->assertJsonStructure(['data' => [['roles' => [['id', 'name', 'permissions']]]]]);
    }
}
