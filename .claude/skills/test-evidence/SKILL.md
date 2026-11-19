---
name: test-evidence
description: The shared report format and severity scale for test runs, reviews and defects in this project. Read by the reviewer and tester agents; use whenever recording a finding or a test result.
---

# Findings and results

## A finding

- **Severity**: BLOCKER (core flow impossible) · CRITICAL (money/stock wrong, data loss, permission only
  enforced client-side) · HIGH (normal user hits a real defect) · MEDIUM (narrow trigger or workaround) ·
  LOW (cosmetic) · INFO (observation). Unsure between two? Say why.
- **Confidence**: CONFIRMED (traced in code or reproduced) or SUSPECTED. Never present a suspicion as fact.
- **Where**: repo (`backend`/`frontend`), file:line, endpoint or component.
- **Expected vs actual**, concretely; **repro steps** from a known state; verbatim error text.
- **Smallest fix** consistent with the project, or "no good targeted fix".

## A test result

Per scenario: PASS / FAIL / BLOCKED / NOT TESTED, with the evidence (command + result, screenshot path,
traced code path). A PASS without evidence is NOT TESTED. Overall verdict: PASS · PASS WITH WARNINGS ·
FAIL · BLOCKED.

## Report layout

```
## Summary        (verdict + the 1–3 things that matter)
## Scope          (what was reviewed/tested)
## Checks         (commands run, results)
## Findings       (by severity)
## Not verified   (and what would verify it)
```

Long runs worth keeping: `test-results/YYYY-MM-DD-<feature>.md`. Otherwise report in chat only.
Redact secrets and real personal data from payloads.
