#!/usr/bin/env bash
set -e

# Ensure disk-backed folders exist
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/{cache,sessions,views}
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/database

# Ensure SQLite DB file exists
if [ ! -f /var/www/storage/database/database.sqlite ]; then
  touch /var/www/storage/database/database.sqlite
fi

# Permissions for Laravel
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true

# Cache configs/views/routes for prod (best effort)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations (idempotent)
php artisan migrate --force || true

# Start supervisor (runs php-fpm + nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf
