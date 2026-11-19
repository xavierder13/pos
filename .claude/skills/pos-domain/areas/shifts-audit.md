# Shifts, cash drawer & audit

**Current state:** not built. Activity log (spatie) and the audit-trail page exist from the starter.

## Model to build

- `shifts`: terminal, cashier, opened_at/closed_at, opening float, expected cash (computed), counted
  cash, difference, notes. One open shift per terminal; selling requires an open shift.
- `cash_movements`: pay-in / pay-out / no-sale open, with reason and user.
- X report (mid-shift, read-only) and Z report (closes the shift, frozen numbers).

## Rules

- Void, refund, price override, big discount, no-sale: permission + audit entry (who, when, before/after).
  A manager override records the approving user, not just the cashier.
- Reports read completed documents only; never recompute history from current prices or rates.
