<?php

namespace App\Http\Resources\Products;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null),
            'unit' => $this->unit,
            'price' => $this->price,
            'cost' => $this->cost,
            'tax_class' => $this->tax_class,
            'sc_pwd_eligible' => $this->sc_pwd_eligible,
            'track_stock' => $this->track_stock,
            'reorder_level' => (float) $this->reorder_level,
            // total on hand across locations (list / view); null for products that don't track stock
            'on_hand' => $this->when(
                array_key_exists('on_hand', $this->getAttributes()),
                fn () => $this->track_stock ? (float) ($this->on_hand ?? 0) : null,
            ),
            'active' => $this->active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
