<?php

namespace Tests\Feature\Inventory;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Tests\Feature\ApiTestCase;

class InventoryTest extends ApiTestCase
{
    private Location $store;

    private Location $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Location::factory()->create(['code' => 'MAIN', 'name' => 'Main Store']);
        $this->warehouse = Location::factory()->create(['code' => 'WH1', 'name' => 'Warehouse', 'type' => 'warehouse']);
    }

    private function onHand(Product $p, Location $l): float
    {
        return (float) StockLevel::where('product_id', $p->id)->where('location_id', $l->id)->value('quantity');
    }

    public function test_receiving_adds_stock_writes_the_ledger_and_updates_cost(): void
    {
        $this->actingAsAdmin();
        $milk = Product::factory()->create(['cost' => 9000]);

        $this->postJson('/api/inventory/receipts', [
            'location_id' => $this->warehouse->id, 'supplier' => 'Dairy Co', 'reference' => 'DR-1001',
            'lines' => [['product_id' => $milk->id, 'quantity' => 24, 'unit_cost' => 9250]],
        ])->assertCreated()->assertJsonPath('data.number', fn ($n) => str_starts_with($n, 'RCV-'))
            ->assertJsonPath('data.movements.0.balance_after', 24);

        $this->assertSame(24.0, $this->onHand($milk, $this->warehouse));
        $this->assertSame(9250, $milk->fresh()->cost);
        $this->getJson("/api/products/{$milk->id}")->assertJsonPath('data.on_hand', 24);
    }

    public function test_weighed_quantities_keep_three_decimals(): void
    {
        $this->actingAsAdmin();
        $rice = Product::factory()->create(['unit' => 'kg']);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [['product_id' => $rice->id, 'quantity' => 10.125]]])->assertCreated();
        $this->postJson('/api/inventory/adjustments', ['location_id' => $this->store->id, 'reason' => 'damaged', 'lines' => [['product_id' => $rice->id, 'quantity' => -0.250]]])->assertCreated();

        $this->assertSame(9.875, $this->onHand($rice, $this->store));
    }

    public function test_adjustment_needs_a_reason_and_cannot_go_below_zero(): void
    {
        $this->actingAsAdmin();
        $soap = Product::factory()->create(['name' => 'Dish Soap']);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [['product_id' => $soap->id, 'quantity' => 3]]]);

        $this->postJson('/api/inventory/adjustments', ['location_id' => $this->store->id, 'lines' => [['product_id' => $soap->id, 'quantity' => -1]]])
            ->assertStatus(422)->assertJsonValidationErrors('reason');
        $this->postJson('/api/inventory/adjustments', ['location_id' => $this->store->id, 'reason' => 'lost', 'lines' => [['product_id' => $soap->id, 'quantity' => -5]]])
            ->assertStatus(422)->assertJsonPath('message', 'Not enough Dish Soap at this location: 3 on hand.');

        // nothing half-written
        $this->assertSame(3.0, $this->onHand($soap, $this->store));
        $this->assertSame(1, StockMovement::count());
    }

    public function test_transfer_moves_stock_between_locations_atomically(): void
    {
        $this->actingAsAdmin();
        $water = Product::factory()->create();
        $towels = Product::factory()->create(['name' => 'Paper Towels']);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->warehouse->id, 'lines' => [
            ['product_id' => $water->id, 'quantity' => 100], ['product_id' => $towels->id, 'quantity' => 2],
        ]]);

        $this->postJson('/api/inventory/transfers', ['location_id' => $this->warehouse->id, 'to_location_id' => $this->store->id, 'lines' => [
            ['product_id' => $water->id, 'quantity' => 40],
        ]])->assertCreated()->assertJsonPath('data.to_location.code', 'MAIN');
        $this->assertSame(60.0, $this->onHand($water, $this->warehouse));
        $this->assertSame(40.0, $this->onHand($water, $this->store));

        // one bad line rolls back the whole transfer
        $this->postJson('/api/inventory/transfers', ['location_id' => $this->warehouse->id, 'to_location_id' => $this->store->id, 'lines' => [
            ['product_id' => $water->id, 'quantity' => 10], ['product_id' => $towels->id, 'quantity' => 5],
        ]])->assertStatus(422);
        $this->assertSame(60.0, $this->onHand($water, $this->warehouse));

        $this->postJson('/api/inventory/transfers', ['location_id' => $this->store->id, 'to_location_id' => $this->store->id, 'lines' => [
            ['product_id' => $water->id, 'quantity' => 1],
        ]])->assertStatus(422)->assertJsonValidationErrors('to_location_id');
    }

    public function test_stock_list_per_location_low_stock_and_ledger(): void
    {
        $this->actingAsAdmin();
        $low = Product::factory()->create(['name' => 'Eggs', 'reorder_level' => 10]);
        $ok = Product::factory()->create(['name' => 'Rice', 'reorder_level' => 5]);
        Product::factory()->create(['name' => 'Delivery', 'track_stock' => false]);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [
            ['product_id' => $low->id, 'quantity' => 4], ['product_id' => $ok->id, 'quantity' => 50],
        ]]);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->warehouse->id, 'lines' => [['product_id' => $low->id, 'quantity' => 30]]]);

        // all locations: services are not listed; eggs total 34
        $this->getJson('/api/inventory/stock')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/inventory/stock?search=Eggs')->assertJsonPath('data.0.quantity', 34);
        // at the store eggs are low
        $this->getJson("/api/inventory/stock?location_id={$this->store->id}&status=low")->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.name', 'Eggs');
        $this->getJson("/api/inventory/low-stock?location_id={$this->store->id}")->assertJsonCount(1, 'data');
        // per location: eggs are low at the store even though the warehouse holds 30
        $this->getJson('/api/inventory/low-stock')->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Eggs')->assertJsonPath('data.0.location.code', 'MAIN');

        $this->getJson("/api/inventory/products/{$low->id}/levels")->assertOk()->assertJsonCount(2, 'data');
        $this->getJson("/api/inventory/movements?product_id={$low->id}")->assertJsonPath('meta.total', 2);
        $this->getJson('/api/inventory/documents?type=receipt')->assertJsonPath('meta.total', 2);
    }

    public function test_services_do_not_track_stock_and_history_blocks_delete(): void
    {
        $this->actingAsAdmin();
        $service = Product::factory()->create(['track_stock' => false, 'name' => 'Gift Wrapping']);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [['product_id' => $service->id, 'quantity' => 1]]])
            ->assertStatus(422)->assertJsonPath('message', 'Gift Wrapping does not track stock.');

        $item = Product::factory()->create();
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [['product_id' => $item->id, 'quantity' => 1]]]);
        $this->deleteJson("/api/products/{$item->id}")->assertStatus(422);
        $this->deleteJson("/api/locations/{$this->store->id}")->assertStatus(422);
    }

    public function test_permissions(): void
    {
        $item = Product::factory()->create();
        $this->actingAsUserWith(['inventory-list']);
        $this->getJson('/api/inventory/stock')->assertOk();
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [['product_id' => $item->id, 'quantity' => 1]]])->assertStatus(403);
        $this->postJson('/api/inventory/adjustments', [])->assertStatus(403);
        $this->postJson('/api/inventory/transfers', [])->assertStatus(403);

        $this->actingAsUserWith(['inventory-receive']);
        $this->getJson('/api/inventory/stock')->assertStatus(403);
        $this->postJson('/api/inventory/receipts', ['location_id' => $this->store->id, 'lines' => [['product_id' => $item->id, 'quantity' => 1]]])->assertCreated();
    }

    public function test_register_catalog_lists_active_products_and_stores(): void
    {
        Product::factory()->create(['name' => 'Sold']);
        Product::factory()->create(['name' => 'Gone', 'active' => false]);

        $this->actingAsUserWith(['register-use']);
        $this->getJson('/api/register/catalog')->assertOk()
            ->assertJsonCount(1, 'products')->assertJsonPath('products.0.name', 'Sold')
            ->assertJsonCount(1, 'locations')->assertJsonPath('locations.0.code', 'MAIN');

        $this->actingAsUserWith(['product-list']);
        $this->getJson('/api/register/catalog')->assertStatus(403);
    }
}
