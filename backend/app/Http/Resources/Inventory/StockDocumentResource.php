<?php

namespace App\Http\Resources\Inventory;

use App\Models\StockDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockDocument */
class StockDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $loc = fn ($l) => $l ? ['id' => $l->id, 'code' => $l->code, 'name' => $l->name] : null;

        return [
            'id' => $this->id,
            'number' => $this->number,
            'type' => $this->type,
            'location' => $loc($this->location),
            'to_location' => $loc($this->toLocation),
            'reason' => $this->reason,
            'supplier' => $this->supplier,
            'reference' => $this->reference,
            'note' => $this->note,
            'user' => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null,
            // products on the document (a transfer writes an out and an in movement per product)
            'lines_count' => $this->whenCounted('movements', fn ($n) => $this->type === 'transfer' ? intdiv($n, 2) : $n),
            'movements' => StockMovementResource::collection($this->whenLoaded('movements')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
