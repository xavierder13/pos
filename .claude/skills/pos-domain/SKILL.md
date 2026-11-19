---
name: pos-domain
description: POS business invariants — money in integer cents, tax, immutable sales and refunds, payments, the stock-movement ledger, shifts and cash-up, offline sync. Load before writing or reviewing any code that touches prices, totals, tax, sales, payments, stock, shifts or the register.
---

# POS domain

The rules a POS can't get wrong. Each area's detail is in `areas/`: read only the one you touch.

| Touching | Read |
|---|---|
| prices, totals, discounts, rounding, tax | `areas/money-tax.md` |
| sales, refunds, voids, payments, receipts | `areas/sales-payments.md` |
| products, variants, modifiers, stock, purchasing | `areas/catalog-inventory.md` |
| shifts, cash drawer, X/Z reports, audit | `areas/shifts-audit.md` |
| offline register, sync, devices, printers | `areas/offline-devices.md` |
| receipts/invoices, VAT, senior/PWD discounts, BIR (target: **Philippines**) | `areas/ph-compliance.md` |

## The invariants (always)

1. **Money is an integer in minor units** (cents) in PHP, JS and the database (`bigint`/`unsignedBigInteger`
   columns named `*_amount` or `price`). Never floats, never `decimal` maths in JS. Frontend formats
   with `utils/money.js` (`formatMoney`, `toMinor`, `toMajor`).
2. **A completed sale never changes.** A void before completion deletes nothing either: it is a status.
   A refund or exchange is a new document that references the original.
3. **Stock is a ledger.** Every change is a `stock_movements` row (sale, refund, receipt, adjustment,
   transfer, count) with a reason and a user; on-hand is the sum (cached, never edited directly).
4. **Totals are computed in one place on the backend** and the register shows the same algorithm
   (`pages/register/useTicket.js` → `totalsOf`). Round once per sale, not per line, unless a tax
   regime says otherwise (then document it in `areas/money-tax.md`).
5. **Sensitive actions are permissioned and audited**: void, refund, price override, discount over the
   limit, no-sale drawer open, stock adjustment. Permission `<module>-<action>` + activity log entry.
6. **IDs that the register creates offline are client-generated** (ULID/UUIDv7), and every write the
   register syncs is idempotent (same ID twice = one record).
7. **Target market is the Philippines**: VAT 12 % inclusive, tax class per line (vatable / exempt /
   zero-rated), statutory discounts (senior/PWD), BIR invoice rules. See `areas/ph-compliance.md`.
8. **Business-type differences are configuration**, not forks: modifiers, weighed items, serials,
   tables, appointments are features switched on per store, sharing the same sale/line model.

## Before you finish

- Are all amounts integers end to end (request → validation → model → resource → UI)?
- Can the change alter a completed sale, or stock without a movement row? It must not.
- New sensitive action → permission seeded + audit log + test that a user without it gets 403.
