<?php

namespace App\Http\Controllers\API\Administration;

use App\Http\Controllers\Controller;
use App\Models\ApprovalFlow;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\SessionSetting;
use App\Models\User;
use App\Support\ListSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

// Audit Trail: who created, changed or deleted what and when
// (models using App\Models\Concerns\AuditsActivity). Newest first.
class AuditTrailController extends Controller
{
    // audited models → the trail's name for them; add yours here
    public const RECORDS = [
        User::class => 'User',
        Role::class => 'Role',
        SessionSetting::class => 'Session Settings',
        ApprovalFlow::class => 'Approval Flow',
        Location::class => 'Location',
        Category::class => 'Category',
        Product::class => 'Product',
        // [make:module records]
    ];

    private const SORTS = ['created_at' => 'created_at', 'module' => 'log_name', 'action' => 'event'];

    // GET /audit-trail?module=&action=&causer_id=&record=&record_id=&search=&date_from=&date_to=&page=&per_page=&sort=&order=
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'module' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', 'in:created,updated,deleted'],
            'causer_id' => ['nullable', 'integer'],
            'record' => ['nullable', 'string', 'max:50', 'required_with:record_id'],
            // one record's history (a view page's Activity): record + record_id
            'record_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ] + ListSort::rules(array_keys(self::SORTS)));

        $subjectType = ($filters['record'] ?? null) ? array_search($filters['record'], self::RECORDS, true) : null;

        $query = Activity::query()
            ->when($filters['module'] ?? null, fn ($q, $v) => $q->where('log_name', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('event', $v))
            ->when($filters['causer_id'] ?? null, fn ($q, $v) => $q->where('causer_type', (new User)->getMorphClass())->where('causer_id', $v))
            ->when($subjectType !== null, fn ($q) => $q->where('subject_type', $subjectType ?: '-'))
            ->when($filters['record_id'] ?? null, fn ($q, $v) => $q->where('subject_id', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('description', 'like', '%'.addcslashes($v, '%_\\').'%'))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', "{$v} 00:00:00"))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', "{$v} 23:59:59"))
            ->orderByDesc('id');

        $page = ListSort::apply($query, $filters, self::SORTS)->paginate(min((int) ($filters['per_page'] ?? 20), 100));
        $users = User::whereIn('id', $page->getCollection()->pluck('causer_id')->filter()->unique())->pluck('name', 'id');

        return response()->json([
            'data' => $page->getCollection()->map(fn (Activity $a) => [
                'id' => $a->id,
                'created_at' => $a->created_at?->toIso8601String(),
                'module' => $a->log_name,
                'action' => $a->event,
                'description' => $a->description,
                'record' => self::RECORDS[$a->subject_type] ?? class_basename((string) $a->subject_type),
                'record_id' => $a->subject_id,
                'user' => $a->causer_id ? ($users[$a->causer_id] ?? "User #{$a->causer_id}") : null,
                'changes' => $this->changes($a),
            ])->values(),
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total()],
        ]);
    }

    // the filters' choices
    public function options(): JsonResponse
    {
        return response()->json(['data' => [
            'modules' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name')->filter()->values(),
            'records' => array_values(self::RECORDS),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]]);
    }

    /**
     * @return array<int, array{field: string, old: mixed, new: mixed}>
     */
    private function changes(Activity $a): array
    {
        $changes = $a->attribute_changes?->toArray() ?? [];
        $new = (array) ($changes['attributes'] ?? []);
        $old = (array) ($changes['old'] ?? []);
        $deleted = $a->event === 'deleted';

        return array_map(fn ($field) => [
            'field' => $field,
            'old' => $deleted ? ($new[$field] ?? null) : ($old[$field] ?? null),
            'new' => $deleted ? null : ($new[$field] ?? null),
        ], array_keys($new + $old));
    }
}
