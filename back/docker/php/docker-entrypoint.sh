#!/bin/sh
set -e

echo "Warming up cache..."
php bin/console cache:warmup

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
