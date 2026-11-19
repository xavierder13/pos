<?php

namespace App\Models;

use App\Models\Concerns\AuditsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

// spatie's Role with the audit trail (config/permission.php → models.role)
class Role extends SpatieRole
{
    use AuditsActivity;

    // spatie's Role is guarded, not fillable: name the audited fields
    protected array $auditAlso = ['name'];

    /**
     * spatie resolves the user model from the role's guard_name, which a new
     * (query-level) instance takes from the request's default guard — under
     * `auth:sanctum` that is `sanctum`, which has no user provider, so
     * withCount('users') failed. Every role here is for Rbac::GUARD: the
     * users are App\Models\User.
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_roles'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.model_morph_key'),
        );
    }
}
