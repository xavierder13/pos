# Roadmap

Status and the user's decisions. Read by heading (`grep -n '^#' docs/roadmap.md`); keep "Now" short and
move shipped items to "Done" as one line each.

## Now

- **Sales module** (next, PH-ready): complete a sale from the register (sale + lines + payments + stock
  movements in one transaction), invoice numbering per terminal, VAT breakdown, senior/PWD discount.

## Next

1. Sales (PH-ready): per-line tax class (vatable / exempt / zero-rated), senior/PWD discount with ID capture,
   sequential invoice numbers per terminal, VAT breakdown. Complete a sale from the register (sale + lines + payments in one transaction), receipts list,
   refunds as new documents.
2. Shifts & cash-up: open/close shift, float, pay-in/out, X/Z reports.
3. Inventory, phase 2: suppliers + purchase orders, stock counts, variants / modifiers.
4. Customers & loyalty; store settings (currency, tax mode, receipt header).
5. Offline register (PWA + IndexedDB queue), then printers/drawer (Tauri or local agent).

## Decisions

- Separate frontend (SPA + Laravel API), no Inertia. (user, 2026-11-19)
- Theme: light navy & coral (from the starter's coral copy), chosen for bright store lighting. (2026-11-19)
- Register is full screen, outside the back-office shell; route `/register`, permission `register-use`. (2026-11-19)
- Target country: **Philippines** (user, 2026-11-19). Defaults PHP / en-PH, VAT 12 % inclusive; BIR rules in
  `.claude/skills/pos-domain/areas/ph-compliance.md`.

- Multiple locations from the start (stores + warehouses). (user, 2026-11-19)
- One GitHub repo for everything: `xavierder13/pos`, branch `master`; old project kept on `legacy-master`. (user, 2026-11-19)

## Open questions (for the user)

- Product / brand name for the website (now the placeholder "Kahero POS")? Where should demo requests go
  (email, CRM, or an API endpoint in this backend)?

- BIR: will the client get the POS accredited / a Permit to Use per machine (shapes invoice numbering,
  X/Z readings, e-journal)? Who confirms the compliance details (accountant / consultant)?
- Are the first clients VAT-registered or non-VAT (percentage tax)?
- First business types to support (retail + F&B suggested)?
- Single store or multi-store / multi-tenant SaaS?

## Done

- 2026-11-19 Project created from laravel-react-starter (coral theme); POS back-office shell (sections,
  Open Register, online pill, clock), store dashboard, full-screen register with demo catalog,
  split payments, hold/resume, discount; `register-use` permission + test.
- 2026-11-19 Locations, categories, products (PH tax class, SC/PWD flag, centavo prices); inventory ledger
  (receive / adjust / transfer, stock per location, documents, movements, low stock per location);
  register on the real catalog with per-class VAT; `make seed-demo`. 72 backend tests.
- 2026-11-19 Marketing website (`website/`, "Kahero POS" placeholder brand, real app screenshots, PH pricing,
  demo form without a backend yet).
- Note: the register loads the whole active catalog in one call; a very large catalog will need
  search-as-you-type.
