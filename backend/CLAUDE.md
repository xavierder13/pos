# CLAUDE.md: backend

Laravel 13 JSON API for the React SPA in `../frontend`. PHP 8.4 in Docker
(`composer.json`: ^8.3). API only: no Blade views, no Vite.

- **Auth**: Sanctum personal access tokens (bearer). Every route but login has `auth:sanctum` +
  `session.activity` (CheckSessionActivity: idle timeout / session duration from the
  `session_settings` table, editable in the SPA, never hard-coded). Routes outside `/auth`
  also have `password.change` (RequirePasswordChange: 403 `code: password_change_required`).
  A request with `X-Session-Check: passive` is checked but doesn't count as activity.
- **Authorization**: spatie/laravel-permission. Every role and permission is on guard `web`
  (`App\Support\Rbac::GUARD`); `User` pins `$guard_name` to it.
- **Audit trail**: spatie/laravel-activitylog v5 through `App\Models\Concerns\AuditsActivity`.
- **Database**: MySQL 8.4 (container `db`). Tests use SQLite in memory (`phpunit.xml`).

## Running it: Docker only

| Service | Container | Host |
|---|---|---|
| php-fpm | `pos_api_app` | — |
| nginx | `pos_api_nginx` | http://localhost:8210 (API at `/api`, health `/up`) |
| MySQL | `pos_api_db` | localhost:3321 |
| phpMyAdmin | `pos_api_phpmyadmin` | http://localhost:8211 |

```
docker compose up -d --build
docker compose exec app php artisan <command>
docker compose exec app composer <command>
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint
```

## Layout

```
app/Http/Controllers/API/<Module>/XController.php   thin: Form Request → Service → Resource
app/Http/Requests/<Module>/*Request.php              validation (authorize() true; routes gate access)
app/Http/Resources/<Module>/*Resource.php            response shape
app/Services/<Module>/*Service.php                   business logic, DB::transaction, BusinessRuleException
app/Models/                                          Eloquent models (Role extends spatie's, audited)
app/Support/Rbac.php, ListSort.php                   constants, server-side column sort
database/seeders/<Module>PermissionSeeder.php        the only source of permission names
routes/api.php                                       every route, grouped per module
```

## Conventions

- **REST**: `GET /things`, `POST /things` (201), `GET|PUT|DELETE /things/{thing}`, plus
  `GET /things/form-options` for a form's choices. Route names `<module>.<action>`.
- **Every route** but login: `auth:sanctum` plus `->middleware('permission:<module>-<action>')`
  (`a|b` = either one). There is no per-module middleware class.
- **Controllers stay thin.** No business logic or queries beyond a trivial lookup.
- **Services** wrap multi-step writes in `DB::transaction()`, use `lockForUpdate()` for
  state other requests may change at the same time, and throw
  `App\Exceptions\BusinessRuleException('user-facing message')` (rendered as 422 `{ message }`,
  or the status passed in). Never return error arrays.
- **Lists** that can grow are paginated server-side: `?page=&per_page=` (max 100), filters,
  and `sort`/`order` via `ListSort::rules()` / `ListSort::apply()`. They respond with
  `{ data, links, meta }` (a Resource collection of a paginator).
- **Status codes**: 401 no or expired token · 403 missing permission (spatie's exception mapped
  in `bootstrap/app.php`) · 404 `{ message }` missing record · 422 validation / business rule.
- **Administrator**: `Gate::before` passes every check. Ownership or status rules in a
  service must also let `hasRole(Rbac::ADMINISTRATOR)` through.
- **Assignments** replace a full list in one call (`PUT /roles/{role}/users { user_ids }`,
  `PUT /permissions/{permission}/roles { role_ids }`): diff against what's there, change only
  what moved, inside a transaction with `lockForUpdate()`. The Administrator role keeps one
  active user and always holds every permission.
- **Audit**: models with `AuditsActivity` log their own field changes. Pivot syncs (roles,
  permissions) don't fire model events: log them with `App\Support\Audit::listChanged()`.
  `GET /audit-trail?record=<Name>&record_id=<id>` is one record's history (the SPA's view-page Activity).
- **Exports**: `response()->streamDownload()` + `fputcsv` over the list's own query
  (`UserService::query()` is shared by the paged list and `GET /users/export`), chunked.
- **Models**: `Model::preventLazyLoading()` outside production, so eager-load with `->with()`.
  Add `use AuditsActivity;` to audit a model and list it in `AuditTrailController::RECORDS`.
- **Secrets** are never logged by the audit trail (`AUDIT_NEVER`), never returned by a
  Resource, and never printed in chat.

## Records, attachments, approvals

- `App\Support\RecordTypes::TYPES` is the registry of records that shared features attach to:
  key (also the permission prefix) → model, label, SPA link, title attribute, approvals on/off.
  `make:module` adds new modules at its marker.
- **Attachments**: the model uses `Concerns\HasAttachments`; routes `/attachments/{type}/{id}`.
  `<type>-list` to see/download, `<type>-edit` to upload/delete. Limits: `config/attachments.php` (.env).
- **Approvals**: flows live in the DB (Administration → Approval Flows). `Services\Approvals\ApprovalService`
  does submit / approve / reject / cancel in transactions with `lockForUpdate()`, notifies, and calls
  the model's optional `onApprovalApproved()` / `onApprovalRejected()`. The Administrator may decide any
  level. Never move a request's status outside the service.
- **Password reset**: `PasswordResetController`; the email links to `config('app.frontend_url')/reset-password`.
  In development every email goes to Mailpit (`MAILPIT_PORT`).

## Notifications

`$user->notify(new App\Notifications\AppNotification($title, $body, $spaLink, $type))` (database
channel → the SPA bell). Subclass it for a named notification (see `RoleAssignedNotification`)
or override `via()` to add mail. `NotificationController` only ever touches the signed-in user's own.

## Adding a module

**Use the generator:** `php artisan make:module Product` (or `make module name=Product` at the
project root, which also runs the frontend half, the migration, and the permission seeder). Templates are
in `stubs/module/`; shared files are extended at their `// [make:module …]` markers in
`routes/api.php`, `PermissionSeeder::MODULES` and `AuditTrailController::RECORDS`. Keep those
markers. By hand, the steps are:

1. Migration + model.
2. `<Module>PermissionSeeder` (copy `AdministrationPermissionSeeder`), added to
   `PermissionSeeder::MODULES`; then `php artisan db:seed --class=PermissionSeeder`.
3. Form Requests, Service, Resource, thin Controller, and a route group in `routes/api.php`.
4. A feature test extending `Tests\Feature\ApiTestCase` (`actingAsAdmin()`,
   `actingAsUserWith([...permissions])`): the happy path, the 403 without the permission,
   the 422 rules. Use a real token (`withToken`) at least once per module: `Sanctum::actingAs`
   skips the guard switch that `auth:sanctum` does.
5. Tell the frontend: the endpoint docs go in its `src/services/<module>/<x>Api.js` header.
