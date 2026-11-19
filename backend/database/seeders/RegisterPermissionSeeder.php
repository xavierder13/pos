<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * The sales register (the cashier's sell screen).
 */
class RegisterPermissionSeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'register-use',
        ];
    }

    public function run(): void
    {
        foreach (self::permissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => Rbac::GUARD]);
        }
    }
}
