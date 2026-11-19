# Decisions

Why things are the way they are. One short entry per real choice, newest last. Read before reversing one.

## D1 · Money as integer minor units (2026-11-19)
Floats lose cents; decimals in JS are floats. Amounts are integers in PHP, JS and the DB; formatting only
at the edge (`utils/money.js`). Weighed quantities may be decimal; money never is.

## D2 · Register outside the back-office shell (2026-11-19)
Cashiers need every pixel and no navigation to wander into. `layout: 'register'` on a route renders it in
`RegisterLayout` (slim bar, idle sign-out kept). It may later become its own offline app (PWA/Tauri).

## D3 · Claude files: small router + lazy skills + agents (2026-11-19)
Root `CLAUDE.md` is loaded every session, so it only routes (~4 KB). Procedures and domain rules are skills
(only their descriptions cost tokens until used); review/testing run in agents (own context). Modelled on
hris-workspace, minus its always-loaded 24 KB CLAUDE.md and the 110 KB roadmap.
