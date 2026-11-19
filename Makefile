# Day-to-day commands for both apps. Everything runs in Docker — the host
# needs no PHP, Composer or Node.   make help

API = docker compose --project-directory backend -f backend/docker-compose.yml
WEB = docker compose --project-directory frontend -f frontend/docker-compose.yml

.DEFAULT_GOAL := help
.PHONY: help setup up down restart ps logs test lint fresh seed seed-demo site artisan composer npm shell web-prod pint module

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

setup: ## First run: build + start both apps, app key, migrate, seed the admin
	@test -f backend/.env || { cp backend/.env.example backend/.env; echo "backend/.env created — set DB_PASSWORD, DB_ROOT_PASSWORD, SEED_ADMIN_PASSWORD, then run make setup again"; exit 1; }
	@test -f frontend/.env || cp frontend/.env.example frontend/.env
	$(API) up -d --build
	@grep -q '^APP_KEY=base64' backend/.env || $(API) exec app php artisan key:generate
	$(API) exec app php artisan migrate --seed --force
	$(WEB) up -d --build
	@$(MAKE) --no-print-directory ps

up: ## Start both apps
	$(API) up -d
	$(WEB) up -d

down: ## Stop both apps (data kept)
	$(WEB) down
	$(API) down

restart: down up ## Restart both apps

ps: ## Show the containers
	@$(API) ps
	@$(WEB) ps

logs: ## Follow the logs of both apps
	@trap 'kill 0' INT; $(API) logs -f --tail=50 & $(WEB) logs -f --tail=50 & wait

test: ## Backend tests (PHPUnit, SQLite in memory)
	$(API) exec app php artisan test

lint: ## Frontend ESLint + backend Pint (check only)
	$(WEB) exec web-dev npm run lint
	$(API) exec app ./vendor/bin/pint --test

pint: ## Format the backend code
	$(API) exec app ./vendor/bin/pint

fresh: ## Drop every table, migrate and seed again (deletes data!)
	$(API) exec app php artisan migrate:fresh --seed --force

site: ## Serve the marketing site (website/) → http://localhost:8213
	docker compose --project-directory website -f website/docker-compose.yml up -d

seed-demo: ## Sample PH store data for development (locations, products, opening stock); skips if products exist
	$(API) exec app php artisan db:seed --class=DemoDataSeeder --force

seed: ## Re-run the seeders (idempotent: new module permissions)
	$(API) exec app php artisan db:seed --force

artisan: ## make artisan c="route:list --path=api"
	$(API) exec app php artisan $(c)

composer: ## make composer c="require vendor/package"
	$(API) exec app composer $(c)

npm: ## make npm c="install dayjs"
	$(WEB) exec web-dev npm $(c)

shell: ## A shell in the PHP container
	$(API) exec app bash

module: ## Scaffold a full module: make module name=Product (backend + frontend + migrate + permissions)
	@test -n "$(name)" || { echo 'Usage: make module name=Product'; exit 1; }
	$(API) exec app php artisan make:module $(name)
	$(WEB) exec -u $$(id -u):$$(id -g) web-dev node scripts/make-module.mjs $(name)
	$(API) exec app ./vendor/bin/pint -q
	$(API) exec app php artisan migrate --force
	$(API) exec app php artisan db:seed --class=PermissionSeeder --force
	@echo "Done. The Administrator sees the new menu entry now; give other roles its permissions in Roles."

web-prod: ## Build + serve the production React build (nginx)
	$(WEB) --profile prod up -d --build web-app
