<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// The header of a stock change: receipt (RCV), adjustment (ADJ), transfer (TRF), opening (OPN).
// Its movements are the lines. Posted documents are final.
#[Fillable(['number', 'type', 'location_id', 'to_location_id', 'reason', 'supplier', 'reference', 'note', 'user_id'])]
class StockDocument extends Model
{
    public const PREFIXES = ['receipt' => 'RCV', 'adjustment' => 'ADJ', 'transfer' => 'TRF', 'opening' => 'OPN'];

    public const REASONS = ['damaged', 'expired', 'lost', 'found', 'correction', 'count', 'internal_use'];

    /** @return HasMany<StockMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
