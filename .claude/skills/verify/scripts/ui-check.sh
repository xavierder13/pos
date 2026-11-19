#!/usr/bin/env bash
# Screenshot pages of the running POS as the seeded admin. Usage: ui-check.sh [/path …]
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../.." && pwd)"
OUT="${TMPDIR:-/tmp}/pos-ui"
mkdir -p "$OUT" && chmod 777 "$OUT"
cp "$(dirname "${BASH_SOURCE[0]}")/ui-check.js" "$OUT/ui-check.js"
PW="$(grep '^SEED_ADMIN_PASSWORD=' "$ROOT/backend/.env" | cut -d= -f2-)"
WEB_PORT="$(grep '^WEB_DEV_PORT=' "$ROOT/frontend/.env" | cut -d= -f2-)"
docker run --rm --network host -e PW="$PW" -e BASE="http://localhost:${WEB_PORT:-5210}" \
  -e PATHS="${*:-/ /register}" -v "$OUT:/out" -w /home/pptruser \
  ghcr.io/puppeteer/puppeteer:latest sh -c 'cp /out/ui-check.js . && node ui-check.js'
echo "Screenshots: $OUT"
