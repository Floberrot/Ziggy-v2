#!/bin/sh
set -e

echo "Warming up cache..."
php bin/console cache:warmup

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Creating log directories..."
mkdir -p /var/log/caddy
mkdir -p /app/var/log
chmod -R 777 /var/log/caddy
chmod -R 777 /app/var/log

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
