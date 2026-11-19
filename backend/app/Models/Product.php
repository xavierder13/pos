<?php

namespace App\Models;

use App\Models\Concerns\AuditsActivity;
use App\Models\Concerns\HasAttachments;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// A sellable item. Money (price, cost) is integer centavos; quantities are decimal(12,3) so weighed
// items work. tax_class decides the VAT line on the invoice (vatable | exempt | zero_rated).
#[Fillable(['sku', 'barcode', 'name', 'description', 'category_id', 'unit', 'price', 'cost', 'tax_class',
    'sc_pwd_eligible', 'track_stock', 'reorder_level', 'active'])]
class Product extends Model
{
    use AuditsActivity, HasAttachments;

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost' => 'integer',
            'sc_pwd_eligible' => 'boolean',
            'track_stock' => 'boolean',
            'reorder_level' => 'decimal:3',
            'active' => 'boolean',
        ];
    }

    public const TAX_CLASSES = ['vatable', 'exempt', 'zero_rated'];

    public const UNITS = ['pc', 'kg', 'g', 'L', 'mL', 'm', 'box', 'pack'];

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

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
        return 'Products';
    }
}
