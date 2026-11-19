<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Tests\Feature\ApiTestCase;

class ProductTest extends ApiTestCase
{
    public function test_list_is_paginated_and_searchable(): void
    {
        $this->actingAsAdmin();
        Product::factory()->create(['name' => 'Findme Item']);
        Product::factory()->count(3)->create();

        $this->getJson('/api/products?search=Findme')->assertOk()->assertJsonPath('meta.total', 1);
        $sku = Product::factory()->create(['sku' => 'BAR-9', 'barcode' => '4801234567890']);
        $this->getJson('/api/products?search=4801234567890')->assertOk()->assertJsonPath('data.0.id', $sku->id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + ['sku' => 'P-1', 'name' => 'First', 'unit' => 'pc', 'price' => 12550, 'tax_class' => 'vatable'];
    }

    public function test_create_show_update_delete(): void
    {
        $this->actingAsAdmin();

        $id = $this->postJson('/api/products', $this->payload(['sku' => 'abc-1', 'description' => 'Hello']))
            ->assertCreated()->assertJsonPath('data.name', 'First')->assertJsonPath('data.sku', 'ABC-1')
            ->assertJsonPath('data.price', 12550)->json('data.id');
        $this->getJson("/api/products/{$id}")->assertOk()->assertJsonPath('data.description', 'Hello')->assertJsonPath('data.on_hand', 0);
        $this->putJson("/api/products/{$id}", $this->payload(['sku' => 'ABC-1', 'name' => 'Renamed', 'active' => false]))
            ->assertOk()->assertJsonPath('data.active', false);
        $this->deleteJson("/api/products/{$id}")->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $id]);
    }

    public function test_validation_and_permissions(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/api/products', [])->assertStatus(422)->assertJsonValidationErrors(['sku', 'name', 'unit', 'price', 'tax_class']);
        // money is integer centavos: a decimal price is refused
        $this->postJson('/api/products', $this->payload(['price' => 125.5]))->assertStatus(422)->assertJsonValidationErrors('price');
        Product::factory()->create(['sku' => 'TAKEN', 'barcode' => '123']);
        $this->postJson('/api/products', $this->payload(['sku' => 'taken', 'barcode' => '123']))->assertStatus(422)->assertJsonValidationErrors(['sku', 'barcode']);

        $this->actingAsUserWith(['product-list']);
        $this->getJson('/api/products')->assertOk();
        $this->postJson('/api/products', $this->payload())->assertStatus(403);
    }

    public function test_changes_are_audited(): void
    {
        $this->actingAsAdmin();
        $id = $this->postJson('/api/products', $this->payload(['name' => 'Audited']))->json('data.id');

        $this->getJson("/api/audit-trail?record=Product&record_id={$id}")->assertOk()->assertJsonPath('meta.total', 1);
    }
}
