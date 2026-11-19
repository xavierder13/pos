<?php

namespace App\Http\Resources\Inventory;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockMovement */
class StockMovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', fn () => ['id' => $this->product->id, 'sku' => $this->product->sku, 'name' => $this->product->name, 'unit' => $this->product->unit]),
            'location' => $this->whenLoaded('location', fn () => ['id' => $this->location->id, 'code' => $this->location->code, 'name' => $this->location->name]),
            'quantity' => (float) $this->quantity,
            'balance_after' => (float) $this->balance_after,
            'unit_cost' => $this->unit_cost,
            'document' => $this->whenLoaded('document', fn () => [
                'id' => $this->document->id,
                'number' => $this->document->number,
                'type' => $this->document->type,
                'reason' => $this->document->reason,
                'reference' => $this->document->reference,
                'supplier' => $this->document->supplier,
                'user' => $this->document->relationLoaded('user') && $this->document->user ? ['id' => $this->document->user->id, 'name' => $this->document->user->name] : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
