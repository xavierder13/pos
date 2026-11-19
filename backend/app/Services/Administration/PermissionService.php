<?php

namespace App\Services\Administration;

use App\Models\Role;
use App\Support\Audit;
use App\Support\Rbac;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionService
{
    /**
     * Every permission with the roles that have it (and each role's user count).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function all(): Collection
    {
        return Permission::where('guard_name', Rbac::GUARD)
            ->with(['roles' => fn ($q) => $q->select('roles.id', 'roles.name')->withCount('users')->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $p) => $this->row($p));
    }

    /**
     * The permission's full list of roles: roles added get it, roles left out
     * lose it. The Administrator role always keeps every permission.
     *
     * @param  array<int>  $roleIds
     * @return array<string, mixed>
     */
    public function assignRoles(Permission $permission, array $roleIds): array
    {
        DB::transaction(function () use ($permission, $roleIds) {
            $admin = Role::findByName(Rbac::ADMINISTRATOR, Rbac::GUARD);
            $want = array_values(array_unique([...array_map('intval', $roleIds), $admin->id]));
            $before = $permission->roles()->lockForUpdate()->pluck('roles.id')->all();
            $changed = [...array_diff($want, $before), ...array_diff($before, $want)];

            foreach (Role::with('permissions')->whereIn('id', $changed)->where('guard_name', Rbac::GUARD)->get() as $role) {
                $old = $role->permissions()->pluck('name')->all();
                in_array($role->id, $want, true) ? $role->givePermissionTo($permission) : $role->revokePermissionTo($permission);
                Audit::listChanged($role, 'permissions', $old, $role->permissions()->pluck('name')->all());
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission->load(['roles' => fn ($q) => $q->select('roles.id', 'roles.name')->withCount('users')->orderBy('name')]);

        return $this->row($permission);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Permission $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            // `user-create` → module `user`
            'module' => str($p->name)->beforeLast('-')->toString(),
            'roles' => $p->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'users_count' => $r->users_count])->values(),
        ];
    }
}
