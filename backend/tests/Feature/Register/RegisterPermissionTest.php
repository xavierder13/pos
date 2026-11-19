<?php

namespace Tests\Feature\Register;

use App\Support\Rbac;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\ApiTestCase;

class RegisterPermissionTest extends ApiTestCase
{
    public function test_register_permission_is_seeded_and_given_to_the_administrator(): void
    {
        $this->assertTrue(Permission::where('name', 'register-use')->where('guard_name', Rbac::GUARD)->exists());
        $this->assertTrue(Role::findByName(Rbac::ADMINISTRATOR, Rbac::GUARD)->hasPermissionTo('register-use'));
    }
}
