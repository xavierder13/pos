<?php

namespace App\Http\Controllers\API\Administration;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\Administration\PermissionService;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

// Permissions are created by the module seeders
// (database/seeders/PermissionSeeder::MODULES), never by hand. Which roles
// have each one can be changed here.
class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    // GET /permissions → { data: [{ id, name, module, roles: [{ id, name, users_count }] }] }
    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->permissions->all()]);
    }

    // GET /permissions/role-options → { roles: [{ id, name, users_count, read_only }] } (the assign-roles picker)
    public function roleOptions(): JsonResponse
    {
        return response()->json(['roles' => Role::where('guard_name', Rbac::GUARD)->withCount('users')->orderBy('name')->get(['id', 'name'])
            ->map(fn (Role $r) => [...$r->only(['id', 'name', 'users_count']), 'read_only' => $r->name === Rbac::ADMINISTRATOR])]);
    }

    // PUT /permissions/{permission}/roles { role_ids: [ids] } → { data: permission } — the permission's full list of roles
    public function assignRoles(Request $request, Permission $permission): JsonResponse
    {
        $data = $request->validate([
            'role_ids' => ['present', 'array'],
            'role_ids.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('guard_name', Rbac::GUARD)],
        ]);

        return response()->json(['data' => $this->permissions->assignRoles($permission, $data['role_ids'])]);
    }
}
