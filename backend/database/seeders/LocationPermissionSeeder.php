<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

// Locations' permissions (listed in PermissionSeeder::MODULES)
class LocationPermissionSeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return ['location-list', 'location-create', 'location-edit', 'location-delete'];
    }

    public function run(): void
    {
        foreach (self::permissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => Rbac::GUARD]);
        }
    }
}
