# POS

A ready-made, dockerized starting point for a new project: a **Laravel 13 API**
(`backend/`) and a **React 19 SPA** (`frontend/`). It already has sign-in,
users, roles and permissions, an audit trail and a profile page. The patterns
come from `sap-b1-api` / `sap-b1-web` and `hris-api` / `reactjs-ant-design`.

| | Stack |
|---|---|
| `backend/` | Laravel 13 · PHP 8.4 (FPM) · MySQL 8.4 · nginx · Sanctum bearer tokens · spatie/laravel-permission · spatie/laravel-activitylog · PHPUnit |
| `frontend/` | React 19 · Vite 8 · Ant Design 6 · React Router 7 · Zustand 5 · axios · dayjs · ESLint 10 |

The host needs only **Docker** (with Compose v2) and `make`. PHP, Composer and Node all run in containers.

## Start a new project

```bash
cd ~/projects/laravel-react-starter
./new-project.sh "Inventory Portal"          # → ~/projects/inventory-portal
cd ../inventory-portal
make setup                                    # build, start, migrate, seed
```

`new-project.sh` copies the starter and:
- renames the containers, volumes, database and app name (`inventory-portal`, `inventory_portal`)
- picks the first **free port block** so it runs next to your other projects (or use `--port-base 8300`)
- writes `backend/.env` and `frontend/.env` with **generated** DB and admin passwords
- runs `git init` in `backend/` and `frontend/` (separate repos, like the API and web repos; `--no-git` skips this)

Options: `--dir <parent>` · `--port-base 8xx0` · `--up` (also runs `make setup`) · `--no-git`.

Sign in as `admin@example.com` with the `SEED_ADMIN_PASSWORD` from `backend/.env`.

### Ports (one block per project)

| | starter (block 8200) | rule for block B |
|---|---|---|
| API (nginx) | http://localhost:8210/api | B |
| phpMyAdmin | http://localhost:8211 | B+1 |
| Mailpit (development email inbox) | http://localhost:8212 | B+2 |
| MySQL | localhost:3321 | 3300 + (B−8000)/10 |
| React dev (Vite, hot reload) | http://localhost:5210 | 5000 + (B−8000) |
| React production build (nginx) | http://localhost:3210 | 3000 + (B−8000) |

## Everyday commands (`make help`)

```bash
make module name=Product                  # ★ scaffold a whole module (see below)
make up / down / restart / ps / logs
make test                                 # backend PHPUnit (SQLite in memory)
make lint                                 # ESLint + Pint --test
make artisan c="make:model Product -m"    # any artisan command
make composer c="require vendor/pkg"
make npm c="install some-package"
make seed                                 # re-run seeders (after adding permissions)
make fresh                                # migrate:fresh --seed (deletes data)
make web-prod                             # serve the production React build
```

Each app also runs on its own: `cd backend && docker compose up -d`, `cd frontend && docker compose up -d`.

## What's included

**Backend**
- `POST /api/auth/login` (rate-limited) · `GET /api/auth/init` · `POST /api/auth/logout` · `PUT /api/auth/profile`
- Users (paged, searchable, sortable, CSV export), Roles (with permissions), Permissions, Audit Trail: REST, Form Requests → Services → API Resources
- Assign one role to many users, and one permission to many roles, in one call, with the changes logged in the audit trail
- **Session Settings** stored in the database (Administration → Session Settings): idle timeout and maximum session length, applied to everyone at once with **no code change**, and enforced per token by the API
- **Require password change at next sign-in** (set per user)
- **In-app notifications**: `$user->notify(new AppNotification('Title', 'Body', '/link', 'success'))`, with an example notification sent when a user gets a role
- **Approval workflow**: flows set up in the UI (Administration → Approval Flows, no code change), with levels approved by a specific user or by anyone with a role. Submit, approve, reject (reason required) or cancel, with row locking, notifications to the next approvers and to the requester, and optional model hooks `onApprovalApproved()` / `onApprovalRejected()`
- **File attachments** on any record: upload/download/delete, type and size limits from `.env`, audit entries, and files deleted with their record
- **Forgot / reset password**: the email links to the SPA; unknown emails get the same answer; rate-limited; a reset signs the user out everywhere
- `App\Support\RecordTypes`: one registry of the records that attachments and approvals work with
- `php artisan make:module` generator (see below)
- RBAC: permissions named `<module>-<action>`, one seeder per module, the `Administrator` role passes every check (`Gate::before`), and the API answers 401 for no/expired token, 403 for a missing permission, 422 for validation or business-rule errors, 404 for a missing record
- Safety rules: you can't delete or deactivate yourself, the last active Administrator can't be removed, the Administrator role is read-only, and a role in use can't be deleted
- Audit trail on any model with the `AuditsActivity` trait
- 50 feature tests
- Dev stack (bind-mounted, phpMyAdmin) + production stack (`docker-compose.prod.yml`: code in the image, `--no-dev`, opcache, nginx on localhost for a reverse proxy)

**Frontend**
- A pure black and aqua-blue theme (AntD dark algorithm plus CSS variables)
- A split-screen login (animated showcase plus a form panel), with caps-lock warning, the session-ended reason, "remember my email", a message for each error status, and sign-in shared across tabs
- Guarded routes, a permission-filtered sidebar, breadcrumbs that include view pages, browser tab titles, Ctrl+K page search, and a user menu
- **List → View → Edit flow**: Users and Roles each have a view page (`/administration/users/:id`, `/administration/roles/:id`) with header actions, detail sections, and the record's **Activity** history
- **Roles & Permissions viewer** ("By role" / "By permission", searchable, expand all), for one role or for a user's roles
- **Assign users to a role** and **assign roles to a permission** with Transfer lists
- **Idle timeout**: a warning with a countdown one minute before ("Stay signed in" / "Sign out"), activity shared across tabs, a heartbeat while the user types, and the reason shown on the login page
- **Approvals** page ("Awaiting me" with a count, "My requests", "All"), a request page with level steps, a timeline and Approve/Reject/Cancel, **Approval Flows** setup, and an `<ApprovalPanel>` for any view page
- **Attachments**: an `<Attachments type="user" recordId={id} />` section with drag & drop and upload progress
- **Forgot password / Reset password** pages in the login style
- **Notification bell**: unread badge (its polling doesn't keep an idle session alive), dropdown, and a Notifications page
- Users, Roles, Permissions, Audit Trail, Session Settings, Notifications, My Profile, 404 and 403 pages
- axios instance (bearer token; a 401 signs out with a reason on the login page), `handleApiError`, `applyFormErrors` (Laravel 422 shown on form fields), `useModalForm`, `PageErrorBoundary`, lazy-loaded pages
- ESLint with two custom rules: no deprecated AntD props, and every Modal/Drawer persistent
- Dev container (Vite) + production image (multi-stage → nginx, SPA fallback, asset caching)

## Adding a module: `make module name=Product`

One command creates the full module, already wired in and following every convention here:

- **Backend:** migration, audited model with attachments, factory, permission seeder (`product-list/create/edit/delete`), Form Requests, Service, API Resource, thin controller, REST routes with `permission:` middleware, and a feature test (4 tests, passing out of the box)
- **Frontend:** API service, store, hook, list page, add/edit dialog, and a view page with **Attachments**, an **Approval** panel, and Activity history, plus its route, menu entry, and breadcrumb
- It is registered in `RecordTypes`, so approval flows can be set up for it right away
- It then runs the migration and seeds the permissions. The Administrator sees the new menu entry right away.

Two-word names work too: `make module name=SalesOrder` gives `/sales-orders`, `sales_orders`, and `sales-order-list`. The generated module starts with `name`, `description`, and `active` fields; add your own in the migration, model, request, resource, and form.
The templates are `backend/stubs/module/*.stub` and `frontend/scripts/module-stubs/*.stub`. Edit them and every future module changes too.

### By hand (what the generator does)

**Backend** (`make artisan c="..."` for the generators)
1. Migration + model in `app/Models` (add `use AuditsActivity;` to audit it, and list it in `AuditTrailController::RECORDS`).
2. `database/seeders/ProductPermissionSeeder.php`, copied from `AdministrationPermissionSeeder` (`product-list`, `product-create`, …), then add it to `PermissionSeeder::MODULES` and run `make seed`.
3. `app/Http/Requests/Products/*Request.php`, `app/Services/Products/ProductService.php` (logic, transactions, `BusinessRuleException`), `app/Http/Resources/Products/ProductResource.php`.
4. A thin `app/Http/Controllers/API/Products/ProductController.php` and its route group in `routes/api.php`, each route with `permission:product-…`.
5. A feature test extending `Tests\Feature\ApiTestCase`.

**Frontend**
1. `src/services/products/productApi.js` (document the endpoints in its header comment).
2. `src/store/productStore.js` + `src/hooks/useProducts.js`.
3. `src/pages/products/ProductIndex.jsx` (+ `ProductFormModal.jsx`), copied from the Users page.
4. Register it in `src/routes/permissionRoutes.jsx` **and** `src/config/menu.jsx`.

## CI

GitHub Actions run on every push to `master` and on pull requests: Pint, PHPUnit, ESLint, and the production build.
The starter repo uses `.github/workflows/ci.yml` (both apps). Projects made with `new-project.sh` get `backend/.github` and `frontend/.github`, one workflow per app repo.

## Production

```bash
# backend: set APP_ENV=production, APP_DEBUG=false, real secrets in .env
cd backend && docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan optimize

# frontend: VITE_API_BASE_URL=https://api.example.com/api in .env (baked in at build)
cd frontend && docker compose -f docker-compose.prod.yml up -d --build
```

Both listen on `127.0.0.1` only. Put a reverse proxy with HTTPS (nginx, Caddy …) in front, and add the SPA's origin to `FRONTEND_URLS`.

## Updating the starter itself

Change things here, then run `make test lint` and commit. Projects already created are not affected.
