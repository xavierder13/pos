<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Every module's permission seeder, then the Administrator role gets every
 * permission. Idempotent: safe to re-run (`php artisan db:seed --class=PermissionSeeder`
 * after adding a module); never takes a permission away from a role.
 */
class PermissionSeeder extends Seeder
{
    /** @var array<int, class-string> */
    public const MODULES = [
        AdministrationPermissionSeeder::class,
        RegisterPermissionSeeder::class,
        LocationPermissionSeeder::class,
        CategoryPermissionSeeder::class,
        ProductPermissionSeeder::class,
        InventoryPermissionSeeder::class,
        // [make:module seeders]
    ];

    /**
     * Every permission name, sorted.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        $names = [];
        foreach (self::MODULES as $seeder) {
            array_push($names, ...$seeder::permissions());
        }
        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->call(self::MODULES);

        Role::firstOrCreate(['name' => Rbac::ADMINISTRATOR, 'guard_name' => Rbac::GUARD])
            ->syncPermissions(Permission::where('guard_name', Rbac::GUARD)->get());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
