<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

// one level of a flow: approved by a user, or by anyone with a role
#[Fillable(['level', 'approver_type', 'approver_id'])]
class ApprovalFlowLevel extends Model
{
    public const USER = 'user';

    public const ROLE = 'role';

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    // may this user decide at this level?
    public function allows(User $user): bool
    {
        return $this->approver_type === self::USER
            ? $user->id === (int) $this->approver_id
            : $user->roles->contains('id', (int) $this->approver_id);
    }

    /**
     * The users who can decide at this level (active accounts).
     *
     * @return Collection<int, User>
     */
    public function approvers(): Collection
    {
        return $this->approver_type === self::USER
            ? User::whereKey($this->approver_id)->where('active', true)->get()
            : User::whereHas('roles', fn ($q) => $q->whereKey($this->approver_id))->where('active', true)->get();
    }

    public function approverName(): string
    {
        return $this->approver_type === self::USER
            ? (User::find($this->approver_id)?->name ?? "User #{$this->approver_id}")
            : 'Any '.(Role::find($this->approver_id)?->name ?? "role #{$this->approver_id}");
    }
}
