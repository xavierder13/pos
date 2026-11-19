---
name: add-feature
description: Build a new POS feature or module end to end (backend + frontend) following this project's conventions, then hand it to the reviewer and tester agents. Use for "add products", "build shifts", "new module X".
argument-hint: <feature and what it should do>
---

# Add a feature

Build `$ARGUMENTS`. If it's empty or the business intent is unclear (which business types, who may do
it, what's out of scope), ask first: one short question round, only real decisions.

## 1. Plan (short, in chat)

- Grep `docs/roadmap.md` headings; read the feature's section and "Decisions" if present.
- Money, sales, stock, tax, shifts → load `pos-domain` and the one `areas/` file it points to.
- List: tables + columns (money as integer cents), endpoints, permissions (`<module>-<action>`),
  pages (List → View → Edit dialog), menu section (Sell / Back office / Settings).
  Non-trivial or several valid designs → show the plan and wait for an OK.

## 2. Scaffold

`make module name=<Model>` from the root generates both halves (migration, model, request,
service, resource, controller, routes, permission seeder, test; service, store, hook, pages, route,
menu entry) at the `[make:module]` markers. Then move the menu entry into the right section in
`frontend/src/config/menu.jsx`.

## 3. Backend (`backend/CLAUDE.md` rules)

Migration → model → Form Request → Service (business rules, transactions) → Resource → controller.
Feature tests: happy path, validation 422, permission 403 for a user without it, Administrator passes,
any `pos-domain` invariant the feature touches (e.g. a completed sale can't be edited).

## 4. Frontend (`frontend/CLAUDE.md` rules)

Service header comment = the real contract. Errors via `handleApiError`; money via `utils/money.js`;
colours via `COLORS`; dialogs `keyboard={false}`. Touch targets ≥ 40px on screens cashiers use.

## 5. Checks, then hand off

Run `make test`, `make pint`, `make lint`, and the build. Then, in this order:
1. `code-reviewer` agent on the change (report-only).
2. `feature-tester` agent (runs the checks + `/verify` screenshots of the new pages).
Fix confirmed findings, then update `docs/roadmap.md` (move the item to Done, one line) and add a
`docs/decisions.md` entry only if a real design choice was made. Commit only when the user asks.
