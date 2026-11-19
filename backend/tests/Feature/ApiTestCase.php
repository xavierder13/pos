<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// Feature tests of the API: every permission seeded, helpers to sign in as
// the Administrator or as a user holding only some permissions.
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ADMINISTRATOR);
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    protected function actingAsUserWith(array $permissions): User
    {
        $role = Role::create(['name' => 'Test '.uniqid(), 'guard_name' => Rbac::GUARD]);
        $role->syncPermissions(Permission::whereIn('name', $permissions)->get());
        $user = User::factory()->create();
        $user->assignRole($role);
        Sanctum::actingAs($user);

        return $user;
    }
}
