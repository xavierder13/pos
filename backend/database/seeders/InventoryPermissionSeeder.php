<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Inventory: see stock + the ledger (list), and the three postings.
 */
class InventoryPermissionSeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'inventory-list',
            'inventory-receive',
            'inventory-adjust',
            'inventory-transfer',
        ];
    }

    public function run(): void
    {
        foreach (self::permissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => Rbac::GUARD]);
        }
    }
}
