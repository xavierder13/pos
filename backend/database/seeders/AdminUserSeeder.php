<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * The first admin account, from SEED_ADMIN_* in .env — no default password.
 * Runs after PermissionSeeder (which creates the Administrator role).
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            throw new RuntimeException('Set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD in .env first.');
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => env('SEED_ADMIN_NAME', 'Administrator'), 'password' => $password],
        );

        $user->assignRole(Role::findByName(Rbac::ADMINISTRATOR, Rbac::GUARD));
    }
}
