#!/usr/bin/env bash
set -o errexit  # stop on first error

echo "Starting Render build process..."

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Create necessary directories
echo "Creating storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p storage/database

# Create SQLite database if it doesn't exist
if [ ! -f storage/database/database.sqlite ]; then
  echo "Creating SQLite database..."
  touch storage/database/database.sqlite
fi

# Set permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
  echo "Generating application key..."
  php artisan key:generate --force
fi

# Laravel optimization
echo "Optimizing Laravel for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if empty
if [ $(php artisan tinker --execute="echo \App\Models\User::count();") -eq 0 ]; then
  echo "Seeding database with initial data..."
  php artisan db:seed --force
fi

# Create storage link
php artisan storage:link

echo "Build process completed successfully!"
