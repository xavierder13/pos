# Catalog & inventory

**Current state** (built 2026-11-19, multi-location from the start):

- `locations` (code, name, type `store|warehouse`, address, active). Registers sell from `store` locations.
- `categories`, `products` (sku, barcode, unit, price/cost in centavos, `tax_class`, `sc_pwd_eligible`,
  `track_stock` false for services, `reorder_level`, active). No variants or modifiers yet.
- Ledger: `stock_documents` (RCV / ADJ / TRF / OPN numbers, reason, supplier, reference, user) →
  `stock_movements` (signed decimal(12,3) qty, `balance_after`, unit_cost) → `stock_levels` cache per
  product × location. All writes go through `App\Services\Inventory\InventoryService::move()`
  (level row `lockForUpdate`, one transaction per document, bcmath for decimals).
- Rules in code: adjustments and transfers refuse going below zero; receipts update the product's cost;
  a product or location with movements can't be deleted (deactivate); low stock is per location
  (`/inventory/low-stock`), never summed.
- Permissions: `location-*`, `category-*`, `product-*`, `inventory-list|receive|adjust|transfer`.
- Frontend: `pages/inventory/` (Stock, Stock documents + view, Movements), `StockDocumentModal`
  (receive / adjust / transfer with product lines), inputs in `components/inputs/` (MoneyInput,
  LocationSelect, ProductPicker). Demo data: `make seed-demo`.

## Still to build

- Variants (size/colour) and F&B modifiers; batch/expiry and serials (opt-in per product).
- Suppliers → purchase orders → receiving against a PO; stock counts (count sheet → variance adjustment).
- Sales deducting stock (a `sale` document type) and refunds restocking.

## Traps

- Barcode lookup must be indexed and exact; scanners send the code + Enter.
- Sales will be allowed to go negative (allow-and-flag) so tills never stop; manual documents may not.
- Quantities are decimals: compare with bccomp in PHP, never `==` on floats.
