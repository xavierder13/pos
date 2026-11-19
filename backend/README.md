# POS: API

Laravel 13 JSON API (Sanctum bearer tokens, spatie permission, activity log),
dockerized. The SPA lives in `../frontend`. See the root README for the overview.

## First run

```bash
cp .env.example .env      # set DB_PASSWORD, DB_ROOT_PASSWORD, SEED_ADMIN_PASSWORD (+ DOCKER_UID/GID = `id -u` / `id -g`)
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

- API: http://localhost:8210/api (health: http://localhost:8210/up)
- phpMyAdmin: http://localhost:8211
- MySQL from the host: localhost:3321

## Endpoints

| Method | Path | Permission |
|---|---|---|
| POST | `/api/auth/login` | (10/min per email+IP) |
| GET | `/api/auth/init` | signed in |
| POST | `/api/auth/logout` | signed in |
| PUT | `/api/auth/profile` | signed in |
| GET | `/api/users` | user-list |
| GET | `/api/users/form-options` | user-create \| user-edit |
| GET | `/api/users/export` | user-list (CSV of the filtered list) |
| POST | `/api/users` | user-create |
| GET | `/api/users/{id}` | user-list \| user-edit |
| PUT | `/api/users/{id}` | user-edit |
| DELETE | `/api/users/{id}` | user-delete |
| GET/POST | `/api/roles`, `/api/roles/form-options` | role-list / role-create |
| GET/PUT/DELETE | `/api/roles/{id}` | role-list / role-edit / role-delete (GET includes the role's users) |
| GET/PUT | `/api/roles/{id}/users` | role-edit (assign the role to many users) |
| GET | `/api/permissions` | permission-list |
| GET | `/api/permissions/role-options` | role-edit |
| PUT | `/api/permissions/{id}/roles` | role-edit (give the permission to many roles) |
| GET | `/api/audit-trail`, `/api/audit-trail/options` | audit-trail-list (`?record=User&record_id=5` = one record's history) |

`php artisan route:list --path=api` lists them all.

## Tests

```bash
docker compose exec app php artisan test
```

## Production

`docker-compose.prod.yml` is a standalone stack: code and `vendor/` (`--no-dev`) are in the
image, opcache is on, there's no phpMyAdmin, MySQL isn't published, and nginx listens on
127.0.0.1 for a reverse proxy. Never run `down -v` on it, because that deletes the database.
