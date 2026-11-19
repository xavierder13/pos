# Website (marketing site)

A static landing page for the POS product (placeholder brand: **Kahero POS**), aimed at Philippine store
owners: features, industries, inventory, BIR-readiness, peso pricing, FAQ, and a demo-request form.
Plain HTML / CSS / JS, no build step.

```
docker compose up -d        # → http://localhost:8213   (or: make site from the project root)
```

- `public/index.html`: the page. The brand name appears in the title, header, footer and copy:
  search for "Kahero" to rename.
- `public/css/site.css`: same palette as the app (navy #172A39, coral #FC563C, off-white).
- `public/js/site.js`: mobile menu, industry tabs, monthly/yearly toggle, scroll reveal, form checks.
- `public/img/*.png`: real screenshots of the app. Refresh them after UI changes (the `/verify` skill's
  `ui-check.js` can be adapted to take them).

**Before going live**: the demo form only shows a thank-you (no backend); connect it to a CRM, email
service or an API endpoint. Prices are samples. "Log in" points at the local dev app (`localhost:5210`).
Features marked "coming soon / rolling out" are on `docs/roadmap.md`; keep the copy honest as they ship.

## v2: the store's own website (`public/v2/` → http://localhost:8213/v2/)

A customer-facing site for the sample business in the demo data (placeholder brand **Suki Corner Café &
Grocery**, Main Store, Quezon City): café menu, grocery shelf with availability, delivery and services,
senior/PWD notice, opening hours with a live open/closed status (Asia/Manila), a drawn map, payments.

- **Live from the POS**: `js/store.js` reads `GET /api/storefront` (the URL is in `<meta name="storefront-api">`).
  Change a price or post stock in the back office and the site follows (cached 60 s).
- That endpoint is **public** and returns customer fields only: names, prices, SC/PWD flag, and an
  availability bucket (`in_stock` / `few_left` / `sold_out`, or `available` for made-to-order and services).
  Never costs, barcodes, SKUs, exact quantities, warehouses or inactive records (covered by `StorefrontTest`).
- **Fallback**: if the API can't be reached it renders `data/storefront.json` (a saved snapshot) and says so.
  Refresh it with `curl -s http://localhost:8210/api/storefront > website/public/v2/data/storefront.json`.
- The API must list the site's origin in `FRONTEND_URLS` (backend `.env`) for CORS: `http://localhost:8213` is there.
- Opening hours, the messenger/phone contact and the brand are in `index.html` / `js/store.js` (`HOURS`);
  item pictures are emoji (Noto Color Emoji web font) until real product photos exist.
