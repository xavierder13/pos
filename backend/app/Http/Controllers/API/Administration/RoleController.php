<?php

namespace App\Http\Controllers\API\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreRoleRequest;
use App\Http\Requests\Administration\UpdateRoleRequest;
use App\Http\Resources\Administration\RoleResource;
use App\Models\Role;
use App\Services\Administration\RoleService;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(
            Role::where('guard_name', Rbac::GUARD)->with(RoleService::WITH)->withCount('users')->orderBy('id')->get(),
        );
    }

    public function formOptions(): JsonResponse
    {
        return response()->json(['permissions' => Permission::where('guard_name', Rbac::GUARD)->orderBy('name')->get(['id', 'name'])]);
    }

    // GET /roles/{role} → { data: Role + users: [{ id, name, email, active }] } (the role's view page)
    public function show(Role $role): RoleResource
    {
        return new RoleResource($this->roles->fresh($role)->load(['users' => fn ($q) => $q->select('users.id', 'name', 'email', 'active')->orderBy('name')]));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        return (new RoleResource($this->roles->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->update($role, $request->validated()));
    }

    // GET /roles/{role}/users → { data: [user ids with the role], users: [{ id, name, email, active, roles: [names] }] }
    public function users(Role $role): JsonResponse
    {
        return response()->json($this->roles->userChoices($role));
    }

    // PUT /roles/{role}/users { user_ids: [ids] } → { data: role } — the role's full list (added get it, left out lose it)
    public function assignUsers(Request $request, Role $role): RoleResource
    {
        $data = $request->validate([
            'user_ids' => ['present', 'array'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        return new RoleResource($this->roles->assignUsers($role, $data['user_ids']));
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roles->delete($role);

        return response()->json(['message' => 'Role deleted.']);
    }
}
