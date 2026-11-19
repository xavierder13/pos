# Philippines: tax & BIR compliance

Target market (user, 2026-11-19). **Current state + rules to build.** These are summaries, not legal
advice: confirm details with the client's accountant / BIR accreditation consultant before building
receipts, and note here what was confirmed and when.

## Settings now

- `VITE_CURRENCY=PHP`, `VITE_LOCALE=en-PH` (frontend `.env`, defaults in compose + `utils/money.js`).
- `frontend/src/utils/tax.js`: `VAT` (12 %, inclusive) + `TAX_CLASSES`. Each product has a `tax_class`;
  the register (`useTicket` → `totalsOf`) spreads the sale discount over the classes and shows VATable
  sales (net), VAT, VAT-exempt and zero-rated sales. Quick cash buttons follow PH notes (20…1000).

## Tax

- **VAT 12 %**, normally VAT-inclusive shelf prices: VAT = amount × 12/112. Round once per invoice.
- Non-VAT-registered sellers (below the VAT threshold, ₱3M gross/year) pay percentage tax instead:
  the store setting needs `vat_registered` yes/no, and non-VAT invoices must not show VAT.
- An invoice separates **VATable sales, VAT amount, VAT-exempt sales, zero-rated sales**. So each line
  needs a tax class: `vatable | exempt | zero_rated`, not just a rate.

## Mandated discounts (they change the tax maths)

- **Senior citizens (RA 9994) and PWD (RA 10754)**: 20 % discount **and** VAT exemption on qualifying
  goods/services (food, medicine, restaurants, etc.). Compute: price ÷ 1.12 (remove VAT) → less 20 %.
  Record the ID number and name on the sale; report these sales separately. Only for qualifying items,
  and the item flag decides what qualifies.
- Others to confirm: national athletes & coaches (RA 10699, 20 %), solo parents (RA 11861, 10 % on
  specific baby items). Build the discount as a configurable "statutory discount" type, not hard-coded.

## Invoices and the POS machine (BIR)

- Since the **EOPT Act (RA 11976, 2024)** the **Sales Invoice** is the primary document for goods and
  services (official receipts are no longer the primary sale document).
- A POS used for invoicing must be **registered with BIR** (accredited software + Permit to Use /
  acknowledgement per machine). The invoice prints: registered name, business style, address,
  **VAT REG TIN + branch code**, MIN (machine ID) / serial number, PTU number, a **sequential,
  gap-free invoice number** per machine, date/time, the VAT breakdown, buyer details when required.
- **X reading** (mid-shift) and **Z reading** (end of day) with a **non-resettable accumulated grand
  total**; an electronic journal of every invoice kept for the retention period.
- Larger taxpayers may be required to send invoices electronically (EIS) and e-sales reports: keep sales
  data structured so an export can be added.
- Voids/cancellations must stay in the journal with a reason; numbers are never reused.

## Payments common in PH

Cash (₱; centavo coins, no mandatory cash rounding), cards via bank terminals, **GCash / Maya**
e-wallets and **QR Ph** codes. Store the e-wallet / card reference number on the payment.

## Privacy

Customer data (loyalty, SC/PWD IDs) falls under the **Data Privacy Act (RA 10173)**: collect only what's
needed, permission-gate access, log who views it.
