<?php

namespace App\Models;

use App\Models\Concerns\AuditsActivity;
use App\Models\Concerns\HasAttachments;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// A place that holds stock: a store (has registers) or a warehouse. type: store | warehouse
#[Fillable(['code', 'name', 'type', 'address', 'active'])]
class Location extends Model
{
    use AuditsActivity, HasAttachments;

    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public const TYPES = ['store', 'warehouse'];

    /** @return HasMany<StockLevel, $this> */
    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    // Approval hooks (optional): called by ApprovalService when this record's request is decided
    // public function onApprovalApproved(\App\Models\ApprovalRequest $request): void {}
    // public function onApprovalRejected(\App\Models\ApprovalRequest $request): void {}

    // the Audit Trail's module
    protected function auditLogName(): string
    {
        return 'Locations';
    }
}
