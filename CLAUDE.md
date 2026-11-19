# CLAUDE.md: POS

A generic point of sale (retail, food & beverage, services) for the **Philippines** (PHP, 12 % VAT
inclusive, BIR rules: `pos-domain` → `areas/ph-compliance.md`): a Laravel 13 API + a React 19 SPA,
Docker only. This file is loaded every session, so it stays a short router: details live in the
files it points to. Read those only when the task needs them.

## Map

One git repo for everything (`xavierder13/pos`, branch `master`; CI in `.github/workflows/ci.yml`).

| Path | What | Read when |
|---|---|---|
| `backend/` | Laravel API | working there: `backend/CLAUDE.md` first |
| `frontend/` | React SPA, light navy & coral theme | working there: `frontend/CLAUDE.md` first |
| `frontend/src/pages/register/` | the full-screen sell screen (real catalog; sales not saved until the Sales module) | touching the register |
| `website/` | v1 POS marketing site ("Kahero POS") at :8213; v2 the sample store's site ("Suki Corner") at :8213/v2/, live from public `GET /api/storefront` | touching a site: `website/README.md` |
| `.claude/skills/pos-domain/` | money, tax, sales, stock, offline rules: the invariants | any sales, stock, money or tax code |
| `docs/roadmap.md` | status, next items, the user's decisions, open questions | planning or starting a feature: grep `^#` headings, read only the relevant section |
| `docs/decisions.md` | why things are the way they are (short, numbered) | before reversing a design choice |
| `test-results/` | evidence from executed test runs | only when asked about a past run |
| `Makefile` | everyday commands (`make help`) | — |

URLs: API http://localhost:8210 · web http://localhost:5210 · phpMyAdmin :8211 · Mailpit :8212 · MySQL :3321 ·
site :8213. Sample data: `make seed-demo`.

## Workflow

- **New feature / module** → `/add-feature` (plan → backend → frontend → checks), then the
  `code-reviewer` and `feature-tester` agents. A builder never certifies its own work.
- **Review** → `/review` (code-reviewer agent; report-only unless told to fix).
- **"Does it work?"** → `/verify` (lint, build, tests, headless-browser screenshots).
- **Frontend ↔ backend mismatch suspected** → `contract-checker` agent.
- Testing / review / investigation is **report-only**; ask before fixing unless the request says fix.

## Rules that span both apps

- **Docker only.** The host has no PHP, Composer or Node: `make artisan c="…"`, `make composer c="…"`,
  `make npm c="…"`, or `docker compose exec` in the app's folder.
- **Money is integer minor units (cents)**, never floats; see `pos-domain`.
- **API contract**: every `frontend/src/services/**/*Api.js` documents its endpoints in a header
  comment; change it together with the backend endpoint and its callers.
- **Permissions** are `<module>-<action>`; the backend seeder is the only source of names; the
  frontend checks the same names (`permissionRoutes.jsx`, `config/menu.jsx`, `can(...)`).
  The `Administrator` role can do everything (backend `Gate::before`, frontend `can()`).
- **Status codes the SPA depends on**: 401 sign out · 403 no permission · 422 validation or business
  rule (`{ message, errors? }`) · 404 missing record.
- **Secrets**: never print `.env` values, tokens or passwords; refer to them indirectly.
- **Safety**: never `make fresh`, drop/truncate, force-push, or discard uncommitted work without an
  explicit request. Say which repo and files you changed.

## Done means

`make test` + `make pint` (backend) · `make lint` + build (frontend) pass, and the feature is
reachable: route, menu entry, permission seeded, test. UI changes: `/verify` screenshots looked at.

## Where facts go (one home each)

| Fact | Home |
|---|---|
| How the user wants work done | auto-memory `feedback` note |
| Status, next items, decisions waiting on the user | `docs/roadmap.md` (not memory) |
| A domain rule or trap (how it works now) | `pos-domain/areas/<area>.md`, edited in place |
| Why it changed | `docs/decisions.md` entry or the commit message |
| An app-wide convention | that app's `CLAUDE.md` |

Keep this file under ~4 KB, a skill's `SKILL.md` under ~6 KB (lookup material goes to files beside
it), and the roadmap's "Now" section short (move finished items to "Done" in one line each).
