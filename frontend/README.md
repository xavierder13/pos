# POS: Web

React 19 + Vite + Ant Design 6 SPA for the API in `../backend`, dockerized.
See the root README for the overview.

## Run

```bash
cp .env.example .env                              # VITE_API_BASE_URL = the API
docker compose up -d --build                      # dev, hot reload → http://localhost:5210
docker compose --profile prod up -d --build       # production build → http://localhost:3210
```

Lint and build:

```bash
docker compose exec web-dev npm run lint
docker compose exec web-dev npm run build
```

## Production

```bash
# .env: VITE_API_BASE_URL=https://api.example.com/api (several, comma-separated, are allowed;
# the one on the page's host wins)
docker compose -f docker-compose.prod.yml up -d --build    # nginx on 127.0.0.1:${WEB_PORT}
```

The API's `FRONTEND_URLS` must include this app's origin (CORS).
