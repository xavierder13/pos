<?php

namespace Tests\Feature\Storefront;

use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Tests\Feature\ApiTestCase;

class StorefrontTest extends ApiTestCase
{
    public function test_public_storefront_shows_customer_fields_and_availability_only(): void
    {
        $store = Location::factory()->create(['code' => 'MAIN', 'name' => 'Main Store']);
        Location::factory()->create(['code' => 'WH1', 'type' => 'warehouse']);
        $plenty = Product::factory()->create(['name' => 'Rice', 'reorder_level' => 5, 'cost' => 9000, 'barcode' => '4800000000001']);
        $few = Product::factory()->create(['name' => 'Eggs', 'reorder_level' => 10]);
        Product::factory()->create(['name' => 'Milk', 'reorder_level' => 5]);   // never stocked here
        Product::factory()->create(['name' => 'Delivery', 'track_stock' => false]);
        Product::factory()->create(['name' => 'Retired', 'active' => false]);
        app(InventoryService::class)->opening(['location_id' => $store->id, 'lines' => [
            ['product_id' => $plenty->id, 'quantity' => 50], ['product_id' => $few->id, 'quantity' => 4],
        ]], User::factory()->create()->id);

        // no sign-in
        $res = $this->getJson('/api/storefront')->assertOk()
            ->assertJsonPath('store.code', 'MAIN')
            ->assertJsonCount(1, 'stores')        // warehouses are not public
            ->assertJsonCount(4, 'products');     // inactive hidden

        $byName = collect($res->json('products'))->keyBy('name');
        $this->assertSame('in_stock', $byName['Rice']['availability']);
        $this->assertSame('few_left', $byName['Eggs']['availability']);
        $this->assertSame('sold_out', $byName['Milk']['availability']);
        $this->assertSame('available', $byName['Delivery']['availability']);

        // nothing internal leaks
        foreach (['cost', 'barcode', 'sku', 'quantity', 'reorder_level', 'track_stock'] as $field) {
            $this->assertArrayNotHasKey($field, $byName['Rice']);
        }
        $this->assertStringNotContainsString('9000', $res->getContent());
    }
}
