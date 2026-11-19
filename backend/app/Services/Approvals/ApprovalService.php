<?php

namespace App\Services\Approvals;

use App\Exceptions\BusinessRuleException;
use App\Models\ApprovalFlow;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use App\Support\Rbac;
use App\Support\RecordTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Moves a record through its approval flow:
 *   submit → (approve at each level …) → approved | rejected | cancelled.
 * The next approvers and the requester are notified at each step. A record
 * model may define onApprovalApproved() / onApprovalRejected() to react
 * (e.g. set its own status).
 */
class ApprovalService
{
    public function activeFlowFor(string $type): ?ApprovalFlow
    {
        return ApprovalFlow::where('record_type', $type)->where('active', true)->with('levels')->latest('id')->first();
    }

    public function submit(string $type, Model $record, User $by, ?string $remarks = null): ApprovalRequest
    {
        if (! RecordTypes::get($type)['approvals']) {
            throw new BusinessRuleException('This kind of record has no approvals.');
        }

        return DB::transaction(function () use ($type, $record, $by, $remarks) {
            $flow = $this->activeFlowFor($type);
            if (! $flow || $flow->levels->isEmpty()) {
                throw new BusinessRuleException('No active approval flow is set up for this kind of record.');
            }
            $pending = ApprovalRequest::whereMorphedTo('approvable', $record)->where('status', ApprovalRequest::PENDING)->lockForUpdate()->exists();
            if ($pending) {
                throw new BusinessRuleException('This record is already waiting for approval.');
            }

            $request = $record->morphMany(ApprovalRequest::class, 'approvable')->create([
                'approval_flow_id' => $flow->id,
                'status' => ApprovalRequest::PENDING,
                'current_level' => 1,
                'total_levels' => $flow->levels->count(),
                'requested_by' => $by->id,
            ]);
            $request->actions()->create(['user_id' => $by->id, 'action' => 'submitted', 'remarks' => $remarks, 'created_at' => now()]);
            $this->notifyApprovers($request);

            return $request;
        });
    }

    public function approve(ApprovalRequest $request, User $by, ?string $remarks = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $by, $remarks) {
            $request = $this->lockPending($request);
            $this->guardApprover($request, $by);

            $request->actions()->create(['level' => $request->current_level, 'user_id' => $by->id, 'action' => 'approved', 'remarks' => $remarks, 'created_at' => now()]);

            if ($request->current_level >= $request->total_levels) {
                $request->update(['status' => ApprovalRequest::APPROVED, 'decided_at' => now()]);
                $this->notifyRequester($request);
                $this->hook($request, 'onApprovalApproved');
            } else {
                $request->update(['current_level' => $request->current_level + 1]);
                $this->notifyApprovers($request->refresh()->load('flow.levels'));
            }

            return $request;
        });
    }

    public function reject(ApprovalRequest $request, User $by, string $remarks): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $by, $remarks) {
            $request = $this->lockPending($request);
            $this->guardApprover($request, $by);

            $request->actions()->create(['level' => $request->current_level, 'user_id' => $by->id, 'action' => 'rejected', 'remarks' => $remarks, 'created_at' => now()]);
            $request->update(['status' => ApprovalRequest::REJECTED, 'decided_at' => now()]);
            $this->notifyRequester($request);
            $this->hook($request, 'onApprovalRejected');

            return $request;
        });
    }

    // the requester (or the Administrator) withdraws a pending request
    public function cancel(ApprovalRequest $request, User $by, ?string $remarks = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $by, $remarks) {
            $request = $this->lockPending($request);
            if ($request->requested_by !== $by->id && ! $by->hasRole(Rbac::ADMINISTRATOR)) {
                throw new BusinessRuleException('Only the requester can cancel this request.', 403);
            }

            $request->actions()->create(['level' => $request->current_level, 'user_id' => $by->id, 'action' => 'cancelled', 'remarks' => $remarks, 'created_at' => now()]);
            $request->update(['status' => ApprovalRequest::CANCELLED, 'decided_at' => now()]);

            return $request;
        });
    }

    // may $user decide this request now? (current level's approver, or the Administrator)
    public function canDecide(ApprovalRequest $request, User $user): bool
    {
        if (! $request->isPending()) {
            return false;
        }

        return $user->hasRole(Rbac::ADMINISTRATOR) || (bool) $request->currentLevel()?->allows($user);
    }

    private function lockPending(ApprovalRequest $request): ApprovalRequest
    {
        $request = ApprovalRequest::whereKey($request->id)->lockForUpdate()->firstOrFail()->load('flow.levels', 'approvable');
        if (! $request->isPending()) {
            throw new BusinessRuleException("This request is already {$request->status}.");
        }

        return $request;
    }

    private function guardApprover(ApprovalRequest $request, User $user): void
    {
        if (! $this->canDecide($request, $user->loadMissing('roles'))) {
            throw new BusinessRuleException('You are not an approver for this level.', 403);
        }
    }

    private function notifyApprovers(ApprovalRequest $request): void
    {
        $approvers = $request->currentLevel()?->approvers() ?? collect();
        Notification::send($approvers, ApprovalNotification::awaiting($request->loadMissing('approvable')));
    }

    private function notifyRequester(ApprovalRequest $request): void
    {
        $request->requester?->notify(ApprovalNotification::decided($request->loadMissing('approvable')));
    }

    private function hook(ApprovalRequest $request, string $method): void
    {
        if ($request->approvable && method_exists($request->approvable, $method)) {
            $request->approvable->{$method}($request);
        }
    }
}
