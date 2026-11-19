# Offline & devices

**Current state:** the register needs the network (it is a page of the SPA). It shows an Online /
Offline pill (`components/StatusClock.jsx`, `hooks/useOnlineStatus.js`) only.

## Direction (agreed: separate frontend, register may become its own app)

- Local catalog cache + sale queue in IndexedDB (Dexie); `vite-plugin-pwa` so the app loads offline.
- Client ULIDs + idempotent `POST /sales` (same id = same sale); per-terminal receipt prefix.
- Conflict policy: the server accepts offline sales even if stock went negative, and flags them.
- Devices: barcode scanners are keyboards (done). Receipt printers / cash drawer (ESC/POS) need a
  local agent or a Tauri wrapper; card terminals go behind a `PaymentProvider` interface per vendor.
