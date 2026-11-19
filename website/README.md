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
