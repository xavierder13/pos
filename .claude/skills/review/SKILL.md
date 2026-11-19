---
name: review
description: Review a change, file or area of this POS for correctness, POS-domain invariants, security and project conventions. Report-only unless asked to fix. Use for "review this", "check my changes", "is this right".
argument-hint: <file, area, or empty for the uncommitted diff in backend/ and frontend/>
---

Review `$ARGUMENTS` (empty → the uncommitted diff: `git -C backend diff` and `git -C frontend diff`,
plus untracked files from `git status --short`).

Delegate to the `code-reviewer` agent with the target and anything the user said about intent.
If the change crosses the API, also run the `contract-checker` agent in parallel.
Relay their findings, highest severity first, without re-reading every file yourself.
Don't modify code unless the user asked for fixes; then fix only CONFIRMED findings and re-run checks.
