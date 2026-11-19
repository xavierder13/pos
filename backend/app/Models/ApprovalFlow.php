<?php

namespace App\Models;

use App\Models\Concerns\AuditsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// a record type's approval chain (levels in order); set up in the SPA
#[Fillable(['name', 'record_type', 'active'])]
class ApprovalFlow extends Model
{
    use AuditsActivity;

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalFlowLevel::class)->orderBy('level');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    protected function auditLogName(): string
    {
        return 'Approvals';
    }
}
