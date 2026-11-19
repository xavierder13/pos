---
name: verify
description: Check that the POS actually works — backend tests, pint, frontend lint and build, then headless-browser screenshots of chosen pages (signed in as the seeded admin) with console errors. Use after a change, for "does it work", "check the UI", or before calling a UI task done.
argument-hint: <optional page paths, e.g. /register /administration/users>
---

# Verify

Run from the project root. Stop at the first failing step and report it (`test-evidence` format).

1. `make test` and `make pint` (backend), `make lint`, then
   `docker compose --project-directory frontend -f frontend/docker-compose.yml exec -T web-dev npm run build`.
   Show only the failing part of any output.
2. UI (skip if no page changed and no paths were given):
   `bash .claude/skills/verify/scripts/ui-check.sh /path1 /path2 …` (default: `/` and `/register`).
   It signs in as the seeded admin (password read from `backend/.env`, never printed), screenshots
   each path at desktop (1440×900) and tablet (820×1180) sizes and prints console errors.
   Screenshots land in `$TMPDIR/pos-ui/`; **look at each one** with Read before saying the UI is right:
   cut-off text, overlapping elements, wrong colours, empty areas.
3. Report: each check's result, what the screenshots show, console errors verbatim.

Needs the containers up (`make up`) and the `ghcr.io/puppeteer/puppeteer` image (pulled on first use).
For a flow (click, type, pay), copy `scripts/ui-check.js` to a scratch file and add the steps;
don't grow the shared script for one-off flows.
