<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

// one submission of a record through its flow (ApprovalService moves it along)
#[Fillable(['approval_flow_id', 'status', 'current_level', 'total_levels', 'requested_by', 'decided_at'])]
class ApprovalRequest extends Model
{
    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public const CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class)->orderBy('id');
    }

    public function currentLevel(): ?ApprovalFlowLevel
    {
        return $this->flow->levels->firstWhere('level', $this->current_level);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
