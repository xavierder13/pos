# === STAGE 1: Build ===
FROM node:22-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
# Vite bakes VITE_* into the bundle at build time
ARG VITE_API_BASE_URL
ARG VITE_APP_NAME
ARG VITE_CURRENCY
ARG VITE_LOCALE
ENV VITE_API_BASE_URL=$VITE_API_BASE_URL
ENV VITE_APP_NAME=$VITE_APP_NAME
ENV VITE_CURRENCY=$VITE_CURRENCY
ENV VITE_LOCALE=$VITE_LOCALE
RUN npm run build

# === STAGE 2: Serve with Nginx ===
FROM nginx:1.28-alpine
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
