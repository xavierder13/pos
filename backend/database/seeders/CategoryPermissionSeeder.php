<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

// Categories' permissions (listed in PermissionSeeder::MODULES)
class CategoryPermissionSeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return ['category-list', 'category-create', 'category-edit', 'category-delete'];
    }

    public function run(): void
    {
        foreach (self::permissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => Rbac::GUARD]);
        }
    }
}
