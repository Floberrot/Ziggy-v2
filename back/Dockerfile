FROM dunglas/frankenphp:1-php8.4 AS base

WORKDIR /app

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    opcache \
    apcu

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Caddyfile
COPY docker/php/Caddyfile /etc/caddy/Caddyfile

# ---- Development stage ----
FROM base AS dev

ENV APP_ENV=dev
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

COPY . .

RUN composer install --no-interaction --optimize-autoloader

EXPOSE 80 443

# ---- Production stage ----
FROM base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 443

ENTRYPOINT ["docker-entrypoint.sh"]
