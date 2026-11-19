<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Illuminate\Database\Seeder;

/**
 * Sample Philippine store data for development: 2 locations, categories, 17 products, opening stock.
 * Never run in production. `make seed-demo` (idempotent: skips when products already exist).
 */
class DemoDataSeeder extends Seeder
{
    public function run(InventoryService $inventory): void
    {
        if (Product::exists()) {
            $this->command?->warn('Products already exist: demo data skipped.');

            return;
        }

        $store = Location::create(['code' => 'MAIN', 'name' => 'Main Store', 'type' => 'store', 'address' => 'Quezon City']);
        $warehouse = Location::create(['code' => 'WH1', 'name' => 'Warehouse', 'type' => 'warehouse', 'address' => 'Valenzuela City']);

        $cat = collect(['Drinks', 'Food', 'Grocery', 'Household', 'Services'])
            ->mapWithKeys(fn ($name) => [$name => Category::create(['name' => $name])->id]);

        // [sku, barcode, name, category, unit, price ₱, cost ₱, tax class, sc/pwd, tracked, reorder, store qty, warehouse qty]
        $rows = [
            ['DRK-001', '4800000000011', 'Americano', 'Drinks', 'pc', 120, 35, 'vatable', true, false, 0, 0, 0],
            ['DRK-002', '4800000000028', 'Cafe Latte', 'Drinks', 'pc', 155, 45, 'vatable', true, false, 0, 0, 0],
            ['DRK-003', '4800000000035', 'Iced Tea', 'Drinks', 'pc', 95, 25, 'vatable', true, false, 0, 0, 0],
            ['DRK-004', '4800000000042', 'Bottled Water 500ml', 'Drinks', 'pc', 25, 12, 'vatable', true, true, 24, 60, 240],
            ['FOD-001', '4800000000059', 'Ensaymada', 'Food', 'pc', 85, 38, 'vatable', true, true, 10, 18, 0],
            ['FOD-002', '4800000000066', 'Chicken Adobo Rice', 'Food', 'pc', 285, 120, 'vatable', true, false, 0, 0, 0],
            ['FOD-003', '4800000000073', 'Pandesal (10 pcs)', 'Food', 'pack', 75, 40, 'vatable', true, true, 10, 6, 0],
            ['FOD-004', '4800000000080', 'Pancit Canton Platter', 'Food', 'pc', 265, 110, 'vatable', true, false, 0, 0, 0],
            ['GRC-001', '4800000000097', 'Whole Milk 1L', 'Grocery', 'pc', 115, 92, 'vatable', true, true, 12, 20, 96],
            ['GRC-002', '4800000000103', 'Eggs (1 dozen)', 'Grocery', 'box', 135, 105, 'exempt', true, true, 10, 8, 60],
            ['GRC-003', '4800000000110', 'Loaf Bread', 'Grocery', 'pc', 180, 120, 'vatable', true, true, 8, 15, 0],
            ['GRC-004', '4800000000127', 'Jasmine Rice 2kg', 'Grocery', 'pack', 165, 130, 'exempt', true, true, 10, 25, 150],
            ['HSH-001', '4800000000134', 'Dish Soap 750ml', 'Household', 'pc', 89, 62, 'vatable', false, true, 6, 4, 48],
            ['HSH-002', '4800000000141', 'Paper Towels (6)', 'Household', 'pack', 215, 160, 'vatable', false, true, 6, 12, 36],
            ['HSH-003', '4800000000158', 'AA Batteries (4)', 'Household', 'pack', 149, 98, 'vatable', false, true, 5, 0, 40],
            ['SRV-001', null, 'Gift Wrapping', 'Services', 'pc', 50, 0, 'vatable', false, false, 0, 0, 0],
            ['SRV-002', null, 'Local Delivery', 'Services', 'pc', 150, 0, 'vatable', false, false, 0, 0, 0],
        ];

        $storeLines = $warehouseLines = [];
        foreach ($rows as [$sku, $barcode, $name, $category, $unit, $price, $cost, $tax, $scPwd, $tracked, $reorder, $storeQty, $whQty]) {
            $product = Product::create([
                'sku' => $sku, 'barcode' => $barcode, 'name' => $name, 'category_id' => $cat[$category], 'unit' => $unit,
                'price' => $price * 100, 'cost' => $cost * 100, 'tax_class' => $tax, 'sc_pwd_eligible' => $scPwd,
                'track_stock' => $tracked, 'reorder_level' => $reorder,
            ]);
            if ($storeQty > 0) {
                $storeLines[] = ['product_id' => $product->id, 'quantity' => $storeQty, 'unit_cost' => $cost * 100];
            }
            if ($whQty > 0) {
                $warehouseLines[] = ['product_id' => $product->id, 'quantity' => $whQty, 'unit_cost' => $cost * 100];
            }
        }

        $userId = User::orderBy('id')->value('id');
        $inventory->opening(['location_id' => $store->id, 'note' => 'Demo opening stock', 'lines' => $storeLines], $userId);
        $inventory->opening(['location_id' => $warehouse->id, 'note' => 'Demo opening stock', 'lines' => $warehouseLines], $userId);
    }
}
