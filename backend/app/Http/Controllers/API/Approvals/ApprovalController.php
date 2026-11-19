<?php

namespace App\Http\Controllers\API\Approvals;

use App\Http\Controllers\Controller;
use App\Models\ApprovalFlowLevel;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\Approvals\ApprovalService;
use App\Support\RecordTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Approval requests. Everyone sees what waits for them and what they
 * requested; `approval-list` sees every request.
 */
class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approvals) {}

    // GET /approvals?scope=awaiting|requested|all&status=&page=&per_page= → { data, meta, awaiting_count }
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'scope' => ['nullable', 'in:awaiting,requested,all'],
            'status' => ['nullable', 'in:pending,approved,rejected,cancelled'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user()->loadMissing('roles');
        $scope = $filters['scope'] ?? 'awaiting';
        abort_if($scope === 'all' && ! $user->can('approval-list'), 403, 'You do not have permission to do that.');

        $query = match ($scope) {
            'awaiting' => $this->awaiting($user),
            'requested' => ApprovalRequest::where('requested_by', $user->id),
            'all' => ApprovalRequest::query(),
        };
        $page = $query->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->with(['approvable', 'flow.levels', 'requester:id,name'])
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20));

        return response()->json([
            'data' => $page->getCollection()->map(fn (ApprovalRequest $r) => $this->row($r, $user))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total()],
            'awaiting_count' => $this->awaiting($user)->count(),
        ]);
    }

    // GET /approvals/{approvalRequest} → { data: request + levels + timeline + can_decide / can_cancel }
    public function show(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $user = $request->user()->loadMissing('roles');
        $this->guardView($approvalRequest, $user);

        return response()->json(['data' => $this->detail($approvalRequest, $user)]);
    }

    // GET /approvals/record/{type}/{id} → { data: latest request (detail) | null, flow_active } (a view page's panel)
    public function forRecord(Request $request, string $type, int $id): JsonResponse
    {
        abort_unless(RecordTypes::exists($type), 404);
        abort_unless($request->user()->can("{$type}-list"), 403, 'You do not have permission to do that.');
        $record = RecordTypes::find($type, $id);
        $latest = ApprovalRequest::whereMorphedTo('approvable', $record)->latest('id')->first();
        $flow = $this->approvals->activeFlowFor($type);

        return response()->json([
            'data' => $latest ? $this->detail($latest, $request->user()->loadMissing('roles')) : null,
            'flow' => $flow ? ['id' => $flow->id, 'name' => $flow->name, 'levels' => $flow->levels->count()] : null,
            'can_submit' => (bool) $flow && $flow->levels->isNotEmpty() && RecordTypes::get($type)['approvals']
                && $request->user()->can("{$type}-edit") && ! ($latest?->isPending()),
        ]);
    }

    // POST /approvals/submit { type, id, remarks? } → 201 { data } (needs <type>-edit)
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string'],
            'id' => ['required', 'integer'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
        abort_unless(RecordTypes::exists($data['type']), 404);
        abort_unless($request->user()->can("{$data['type']}-edit"), 403, 'You do not have permission to do that.');

        $approval = $this->approvals->submit($data['type'], RecordTypes::find($data['type'], $data['id']), $request->user(), $data['remarks'] ?? null);

        return response()->json(['message' => 'Submitted for approval.', 'data' => $this->detail($approval, $request->user())], 201);
    }

    // POST /approvals/{approvalRequest}/approve { remarks? }
    public function approve(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $remarks = $request->validate(['remarks' => ['nullable', 'string', 'max:2000']])['remarks'] ?? null;
        $approval = $this->approvals->approve($approvalRequest, $request->user(), $remarks);

        return response()->json(['message' => $approval->status === ApprovalRequest::APPROVED ? 'Approved.' : 'Approved — sent to the next level.', 'data' => $this->detail($approval, $request->user())]);
    }

    // POST /approvals/{approvalRequest}/reject { remarks } (required)
    public function reject(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $remarks = $request->validate(['remarks' => ['required', 'string', 'max:2000']], ['remarks.required' => 'Give a reason for rejecting.'])['remarks'];

        return response()->json(['message' => 'Rejected.', 'data' => $this->detail($this->approvals->reject($approvalRequest, $request->user(), $remarks), $request->user())]);
    }

    // POST /approvals/{approvalRequest}/cancel { remarks? }
    public function cancel(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $remarks = $request->validate(['remarks' => ['nullable', 'string', 'max:2000']])['remarks'] ?? null;

        return response()->json(['message' => 'Request cancelled.', 'data' => $this->detail($this->approvals->cancel($approvalRequest, $request->user(), $remarks), $request->user())]);
    }

    // pending requests whose current level the user approves (by user or by one of their roles)
    private function awaiting(User $user): Builder
    {
        $roleIds = $user->roles->pluck('id')->all();

        return ApprovalRequest::where('status', ApprovalRequest::PENDING)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('approval_flow_levels')
                ->whereColumn('approval_flow_levels.approval_flow_id', 'approval_requests.approval_flow_id')
                ->whereColumn('approval_flow_levels.level', 'approval_requests.current_level')
                ->where(fn ($q) => $q
                    ->where(fn ($q) => $q->where('approver_type', ApprovalFlowLevel::USER)->where('approver_id', $user->id))
                    ->orWhere(fn ($q) => $q->where('approver_type', ApprovalFlowLevel::ROLE)->whereIn('approver_id', $roleIds ?: [0]))));
    }

    private function guardView(ApprovalRequest $r, User $user): void
    {
        $allowed = $user->can('approval-list') || $r->requested_by === $user->id
            || $r->flow->levels->contains(fn (ApprovalFlowLevel $l) => $l->allows($user));
        abort_unless($allowed, 403, 'You do not have permission to do that.');
    }

    /**
     * @return array<string, mixed>
     */
    private function row(ApprovalRequest $r, User $user): array
    {
        return [
            'id' => $r->id,
            'record' => RecordTypes::describe($r->approvable),
            'flow' => $r->flow?->name,
            'status' => $r->status,
            'current_level' => $r->current_level,
            'total_levels' => $r->total_levels,
            'requested_by' => $r->requester?->name,
            'created_at' => $r->created_at?->toIso8601String(),
            'decided_at' => $r->decided_at?->toIso8601String(),
            'can_decide' => $this->approvals->canDecide($r, $user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(ApprovalRequest $r, User $user): array
    {
        $r->loadMissing(['approvable', 'flow.levels', 'requester:id,name', 'actions.user:id,name']);
        $decisions = $r->actions->whereIn('action', ['approved', 'rejected'])->keyBy('level');

        return $this->row($r, $user) + [
            'can_cancel' => $r->isPending() && ($r->requested_by === $user->id || $user->hasRole('Administrator')),
            'levels' => $r->flow->levels->map(fn (ApprovalFlowLevel $l) => [
                'level' => $l->level,
                'approver' => $l->approverName(),
                'state' => match (true) {
                    $decisions->has($l->level) => $decisions->get($l->level)->action,
                    $r->isPending() && $l->level === $r->current_level => 'current',
                    default => 'waiting',
                },
                'decided_by' => $decisions->get($l->level)?->user?->name,
            ])->values(),
            'timeline' => $r->actions->map(fn ($a) => [
                'id' => $a->id,
                'action' => $a->action,
                'level' => $a->level,
                'user' => $a->user?->name,
                'remarks' => $a->remarks,
                'created_at' => $a->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
