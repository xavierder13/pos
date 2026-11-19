<?php

namespace App\Services\Administration;

use App\Exceptions\BusinessRuleException;
use App\Models\Role;
use App\Models\User;
use App\Notifications\RoleAssignedNotification;
use App\Support\Audit;
use App\Support\ListSort;
use App\Support\Rbac;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class UserService
{
    // what a UserResource shows: roles, each with its permissions (the SPA's Roles & Permissions viewer)
    public const WITH = ['roles:id,name', 'roles.permissions:id,name'];

    // the list's column-header sort keys
    public const SORTS = ['name', 'email', 'active', 'last_login_at', 'created_at'];

    /**
     * @param  array{search?: string|null, active?: bool|null, role_id?: int|null, per_page?: int|null, sort?: string|null, order?: string|null}  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)->with(self::WITH)->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    /**
     * The list's query with its filters and sort (the paged list and the CSV export share it).
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<User>
     */
    public function query(array $filters): Builder
    {
        $query = User::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"),
            ))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']))
            ->when($filters['role_id'] ?? null, fn ($q, $roleId) => $q->whereHas('roles', fn ($q) => $q->whereKey($roleId)))
            ->orderBy('name');

        return ListSort::apply($query, $filters, array_combine(self::SORTS, self::SORTS));
    }

    /**
     * @param  array{name: string, email: string, password: string, active?: bool, roles: array<int>}  $data
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'active' => $data['active'] ?? true,
                'must_change_password' => $data['must_change_password'] ?? false,
            ]);
            $user->syncRoles($this->roles($data['roles']));
            Audit::listChanged($user, 'roles', [], $this->roleNames($user));
            $this->notifyNewRoles($user, [], $this->roleNames($user));

            return $user->load(self::WITH);
        });
    }

    /**
     * @param  array{name: string, email: string, password?: string|null, active: bool, roles: array<int>}  $data
     */
    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $roles = $this->roles($data['roles']);

            if ($user->is($actor) && ! $data['active']) {
                throw new BusinessRuleException('You cannot deactivate your own account.');
            }
            if (! ($roles->contains('name', Rbac::ADMINISTRATOR) && $data['active'])) {
                $this->guardLastAdministrator($user, 'remove the Administrator role from or deactivate');
            }

            $rolesBefore = $this->roleNames($user);
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'active' => $data['active'],
            ]);
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }
            if (array_key_exists('must_change_password', $data)) {
                $user->must_change_password = (bool) $data['must_change_password'];
            }
            $passwordChanged = $user->isDirty('password');
            $user->save();
            $user->syncRoles($roles);
            Audit::listChanged($user, 'roles', $rolesBefore, $this->roleNames($user));
            $this->notifyNewRoles($user, $rolesBefore, $this->roleNames($user));

            // deactivated, or password reset by someone else: signed out everywhere
            if (! $user->active || ($passwordChanged && ! $user->is($actor))) {
                $user->tokens()->delete();
            }

            return $user->load(self::WITH);
        });
    }

    public function delete(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            if ($user->is($actor)) {
                throw new BusinessRuleException('You cannot delete your own account.');
            }
            $this->guardLastAdministrator($user, 'delete');

            $user->tokens()->delete();
            $user->delete();
        });
    }

    /**
     * The signed-in user's own name and password. A new password signs out
     * the user's other sessions (this one stays).
     *
     * @param  array{name: string, password?: string|null}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->name = $data['name'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
            // "Require password change at next sign-in" done
            $user->must_change_password = false;
        }
        $passwordChanged = $user->isDirty('password');
        $user->save();

        if ($passwordChanged) {
            $current = $user->currentAccessToken();
            $user->tokens()
                ->when($current instanceof PersonalAccessToken, fn ($q) => $q->whereKeyNot($current->getKey()))
                ->delete();
        }

        return $user;
    }

    /**
     * Tell the user about each role they just got (in-app notification).
     *
     * @param  array<int, string>  $before
     * @param  array<int, string>  $after
     */
    public static function notifyNewRoles(User $user, array $before, array $after): void
    {
        foreach (array_diff($after, $before) as $role) {
            $user->notify(new RoleAssignedNotification($role));
        }
    }

    /**
     * @return array<int, string>
     */
    private function roleNames(User $user): array
    {
        return $user->roles()->pluck('name')->all();
    }

    /**
     * @param  array<int>  $ids
     * @return Collection<int, Role>
     */
    private function roles(array $ids): Collection
    {
        return Role::whereIn('id', $ids)->where('guard_name', Rbac::GUARD)->get();
    }

    // There must always be one active Administrator left to manage access.
    private function guardLastAdministrator(User $user, string $action): void
    {
        if (! $user->active || ! $user->hasRole(Rbac::ADMINISTRATOR)) {
            return;
        }

        $others = User::role(Rbac::ADMINISTRATOR)
            ->where('active', true)
            ->whereKeyNot($user->id)
            ->lockForUpdate()
            ->count();

        if ($others === 0) {
            throw new BusinessRuleException("You cannot {$action} the last active Administrator.");
        }
    }
}
