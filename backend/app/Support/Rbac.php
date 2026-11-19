<?php

namespace App\Support;

/**
 * Role-based access control constants (spatie/laravel-permission).
 *
 * Permissions are named `<module>-<action>` (list / create / edit / delete,
 * plus module-specific actions) and are created by each module's permission
 * seeder (database/seeders/PermissionSeeder::MODULES). The Administrator role
 * passes every check (Gate::before in AppServiceProvider) and is also kept
 * holding every permission, so its `user_permissions` list is complete.
 */
final class Rbac
{
    public const ADMINISTRATOR = 'Administrator';

    // Roles and permissions are stored for the `web` guard; User pins
    // $guard_name to it so spatie keeps working under `auth:sanctum`.
    public const GUARD = 'web';
}
