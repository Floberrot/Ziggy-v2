# ---- Frontend build ----
FROM node:22-alpine AS frontend

WORKDIR /app
COPY front/package.json front/package-lock.json ./
RUN npm ci
COPY front/ .
RUN npm run build
# Output: /app/public/build/

# ---- PHP base ----
FROM dunglas/frankenphp:1-php8.4 AS base

WORKDIR /app

RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    opcache \
    apcu

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY back/docker/php/Caddyfile /etc/caddy/Caddyfile

# ---- Production ----
FROM base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY back/ .

RUN composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative

# Copy built Vue SPA into Symfony public dir
COPY --from=frontend /app/public/build /app/public/build

COPY back/docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
