#!/usr/bin/env bash
set -o errexit  # stop on first error

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Laravel optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate:fresh --seed

# Run migrations (if you want them in build step)
# php artisan migrate --force
