<?php

namespace App\Services\Administration;

use App\Exceptions\BusinessRuleException;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use App\Support\Rbac;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public const WITH = ['permissions:id,name'];

    /**
     * @param  array{name: string, permissions: array<int>}  $data
     */
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name'], 'guard_name' => Rbac::GUARD]);
            $role->syncPermissions($this->permissions($data['permissions']));
            Audit::listChanged($role, 'permissions', [], $this->permissionNames($role));

            return $this->fresh($role);
        });
    }

    /**
     * @param  array{name: string, permissions: array<int>}  $data
     */
    public function update(Role $role, array $data): Role
    {
        $this->guardAdministrator($role, 'change');

        return DB::transaction(function () use ($role, $data) {
            $before = $this->permissionNames($role);
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($this->permissions($data['permissions']));
            Audit::listChanged($role, 'permissions', $before, $this->permissionNames($role));

            return $this->fresh($role);
        });
    }

    public function delete(Role $role): void
    {
        $this->guardAdministrator($role, 'delete');

        if ($role->users()->exists()) {
            throw new BusinessRuleException('This role is still assigned to users. Remove it from them first.');
        }

        $role->delete();
    }

    /**
     * Every user, for the "assign users" picker: { data: [ids holding the role], users: [...] }.
     *
     * @return array{data: Collection<int, int>, users: Collection<int, array<string, mixed>>}
     */
    public function userChoices(Role $role): array
    {
        return [
            'data' => $role->users()->pluck('users.id'),
            'users' => User::with('roles:id,name')->orderBy('name')->get(['id', 'name', 'email', 'active'])
                ->map(fn (User $u) => [...$u->only(['id', 'name', 'email', 'active']), 'roles' => $u->roles->pluck('name')->sort()->values()]),
        ];
    }

    /**
     * The role's full list of users: users added get the role, users left out
     * lose it (their other roles stay). The Administrator role keeps at least
     * one active user.
     *
     * @param  array<int>  $userIds
     */
    public function assignUsers(Role $role, array $userIds): Role
    {
        return DB::transaction(function () use ($role, $userIds) {
            $before = $role->users()->lockForUpdate()->pluck('users.id')->all();
            $want = array_values(array_unique(array_map('intval', $userIds)));
            $added = array_values(array_diff($want, $before));
            $removed = array_values(array_diff($before, $want));

            if ($role->name === Rbac::ADMINISTRATOR && $removed
                && User::whereIn('id', $want)->where('active', true)->doesntExist()) {
                throw new BusinessRuleException('At least one active user must keep the Administrator role.');
            }

            foreach (User::with('roles:id,name')->whereIn('id', [...$added, ...$removed])->get() as $user) {
                $old = $user->roles->pluck('name')->all();
                in_array($user->id, $added, true) ? $user->assignRole($role) : $user->removeRole($role);
                $new = $user->roles()->pluck('name')->all();
                Audit::listChanged($user, 'roles', $old, $new);
                UserService::notifyNewRoles($user, $old, $new);
            }

            return $this->fresh($role);
        });
    }

    public function fresh(Role $role): Role
    {
        return $role->load(self::WITH)->loadCount('users');
    }

    // The Administrator role holds every permission (PermissionSeeder) and passes every check — it is read only.
    private function guardAdministrator(Role $role, string $action): void
    {
        if ($role->name === Rbac::ADMINISTRATOR) {
            throw new BusinessRuleException("You cannot {$action} the Administrator role.", 403);
        }
    }

    /**
     * @return array<int, string>
     */
    private function permissionNames(Role $role): array
    {
        return $role->permissions()->pluck('name')->all();
    }

    /**
     * @param  array<int>  $ids
     */
    private function permissions(array $ids)
    {
        return Permission::whereIn('id', $ids)->where('guard_name', Rbac::GUARD)->get();
    }
}
