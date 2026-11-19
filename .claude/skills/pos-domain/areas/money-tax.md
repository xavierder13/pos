# Money & tax

**Current state** (edit in place when it changes).

- Currency + locale: `VITE_CURRENCY` / `VITE_LOCALE` in `frontend/.env` for now (default PHP / en-PH).
  Planned: a store setting from the API, with the env as fallback.
- `utils/money.js`: `formatMoney(minor)`, `toMinor(major)` (rounds half away from zero), `toMajor`,
  `MINOR_DIGITS` (0 for JPY), `CURRENCY_SYMBOL`.
- Register totals (`useTicket.js` → `totalsOf(sale, { rate, inclusive })`): subtotal = Σ price × qty;
  discount = round(subtotal × pct); taxable = subtotal − discount. Inclusive (PH VAT, the demo):
  per tax class: the discount is spread by value, VAT = round(vatable × rate / (1 + rate)).
  Exclusive: tax = round(vatable × rate), added to the total.
- PH specifics (VAT classes, senior/PWD maths, invoice fields): `ph-compliance.md`.

## Rules for the real implementation

- Store prices as entered: either tax-inclusive or tax-exclusive per store setting; never both.
- Tax rates are records (name, rate in basis points: 1200 = 12 %, inclusive flag), assigned per product
  or category; a line stores the rate it was sold at (history must not change when rates do).
- Line-level discount and sale-level discount both exist in real stores; store each on the line it
  affects, allocated so lines sum exactly to the sale total (put the rounding remainder on the last line).
- Cash rounding (e.g. to 0.05) applies to the cash tender only, recorded as its own adjustment line.
- Fiscal rules: the target is the Philippines (BIR), see `ph-compliance.md`.
