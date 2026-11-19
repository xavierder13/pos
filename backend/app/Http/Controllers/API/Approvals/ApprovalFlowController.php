<?php

namespace App\Http\Controllers\API\Approvals;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowLevel;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use App\Support\Rbac;
use App\Support\RecordTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Administration → Approval Flows (approval-flow-edit): which record types need
 * approval and who approves at each level. Stored in the database — no code
 * change. Activating a flow deactivates the type's other flows.
 */
class ApprovalFlowController extends Controller
{
    // GET /approval-flows → { data: [Flow] }
    public function index(): JsonResponse
    {
        return response()->json(['data' => ApprovalFlow::with('levels')->withCount(['requests as pending_count' => fn ($q) => $q->where('status', ApprovalRequest::PENDING)])
            ->orderBy('record_type')->orderByDesc('active')->get()->map(fn ($f) => $this->row($f))]);
    }

    // GET /approval-flows/options → { record_types, users, roles }
    public function options(): JsonResponse
    {
        return response()->json([
            'record_types' => RecordTypes::approvalOptions(),
            'users' => User::where('active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'roles' => Role::where('guard_name', Rbac::GUARD)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Approval flow added.', 'data' => $this->row($this->save(new ApprovalFlow, $this->validated($request)))], 201);
    }

    public function update(Request $request, ApprovalFlow $approvalFlow): JsonResponse
    {
        return response()->json(['message' => 'Approval flow saved.', 'data' => $this->row($this->save($approvalFlow, $this->validated($request)))]);
    }

    public function destroy(ApprovalFlow $approvalFlow): JsonResponse
    {
        if ($approvalFlow->requests()->exists()) {
            throw new BusinessRuleException('This flow has requests. Deactivate it instead of deleting it.');
        }
        $approvalFlow->delete();

        return response()->json(['message' => 'Approval flow deleted.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'record_type' => ['required', Rule::in(array_column(RecordTypes::approvalOptions(), 'value'))],
            'active' => ['sometimes', 'boolean'],
            'levels' => ['required', 'array', 'min:1', 'max:10'],
            'levels.*.approver_type' => ['required', 'in:user,role'],
            'levels.*.approver_id' => ['required', 'integer'],
        ], ['levels.required' => 'Add at least one approval level.', 'levels.min' => 'Add at least one approval level.']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function save(ApprovalFlow $flow, array $data): ApprovalFlow
    {
        foreach ($data['levels'] as $i => $level) {
            $exists = $level['approver_type'] === ApprovalFlowLevel::USER
                ? User::whereKey($level['approver_id'])->exists()
                : Role::whereKey($level['approver_id'])->exists();
            if (! $exists) {
                throw new BusinessRuleException('Level '.($i + 1).': choose an existing approver.');
            }
        }

        return DB::transaction(function () use ($flow, $data) {
            if ($flow->exists && $flow->requests()->where('status', ApprovalRequest::PENDING)->exists()
                && count($data['levels']) !== $flow->levels()->count()) {
                throw new BusinessRuleException('Requests are waiting in this flow: you can change who approves, not the number of levels.');
            }
            $flow->fill(['name' => $data['name'], 'record_type' => $data['record_type'], 'active' => $data['active'] ?? true])->save();
            $before = $flow->levels()->get()->map(fn ($l) => $l->approverName())->all();
            $flow->levels()->delete();
            foreach (array_values($data['levels']) as $i => $level) {
                $flow->levels()->create(['level' => $i + 1, 'approver_type' => $level['approver_type'], 'approver_id' => $level['approver_id']]);
            }
            Audit::record($flow, 'updated', ['levels' => $flow->levels()->get()->map(fn ($l) => $l->approverName())->all()], ['levels' => $before], 'Approvals');

            // one active flow per record type
            if ($flow->active) {
                ApprovalFlow::where('record_type', $flow->record_type)->whereKeyNot($flow->id)->update(['active' => false]);
            }

            return $flow->load('levels');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function row(ApprovalFlow $f): array
    {
        return [
            'id' => $f->id,
            'name' => $f->name,
            'record_type' => $f->record_type,
            'record_label' => RecordTypes::exists($f->record_type) ? RecordTypes::get($f->record_type)['label'] : $f->record_type,
            'active' => $f->active,
            'pending_count' => $f->pending_count ?? 0,
            'levels' => $f->levels->map(fn (ApprovalFlowLevel $l) => [
                'level' => $l->level, 'approver_type' => $l->approver_type, 'approver_id' => (int) $l->approver_id, 'approver' => $l->approverName(),
            ])->values(),
        ];
    }
}
