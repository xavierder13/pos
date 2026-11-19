# Sales & payments

**Current state:** no Sales module yet. The register (`frontend/src/pages/register/`) loads the real
catalog (`GET /register/catalog`), picks a store location, keeps the sale in `useTicket` (lines, % discount, held sales in memory) and
`PaymentModal` takes split tenders (cash may exceed the balance → change; card/e-wallet capped).
Completing a sale only clears the screen.

## Model to build (agreed direction, adjust with the user)

- `sales`: ulid id (client-made), number (per-terminal sequence), store, terminal, shift, cashier,
  customer?, status `open|held|completed|voided`, subtotal/discount/tax/total (integers), timestamps.
- `sale_lines`: product snapshot (name, sku, price, tax rate at sale time), qty (decimal(12,3) for weighed
  items), line discount, line total.
- `payments`: sale, method, amount, tendered, change, reference (card auth / e-wallet ref), status.
- `refunds` (or sales with `type=refund` + `original_sale_id`): never edit the original; restock creates
  stock movements.
- Held sales live on the server so another terminal can pick them up.

## Traps

- Paid ≥ total is the completion rule; only cash can overpay.
- Completing a sale = one DB transaction: sale + lines + payments + stock movements + receipt number.
- Receipt numbers must stay gap-free if a fiscal regime requires it: allocate at completion, not at open.
