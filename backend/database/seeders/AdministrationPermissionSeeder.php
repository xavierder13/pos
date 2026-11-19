<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Administration's permissions. Template for a new module's seeder: list its
 * `<module>-<action>` names in permissions(), add the class to
 * PermissionSeeder::MODULES.
 */
class AdministrationPermissionSeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'user-list', 'user-create', 'user-edit', 'user-delete',
            'role-list', 'role-create', 'role-edit', 'role-delete',
            'permission-list',
            'audit-trail-list',
            'session-setting-edit',
            'approval-flow-edit',
            'approval-list',
        ];
    }

    public function run(): void
    {
        foreach (self::permissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => Rbac::GUARD]);
        }
    }
}
