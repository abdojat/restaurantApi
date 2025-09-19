#!/usr/bin/env bash
set -o errexit  # stop on first error

echo "🏪 Shami Restaurant - Render Build Process"
echo "==========================================="

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Create necessary directories
echo "📁 Creating storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p storage/database
mkdir -p bootstrap/cache

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
  echo "🔑 Generating application key..."
  php artisan key:generate --force
fi

# Clear any existing cache
echo "🧹 Clearing existing cache..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Laravel optimization for production
echo "⚡ Optimizing Laravel for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Note: Database operations will be handled by the Docker container startup
echo "📝 Note: Database migrations and seeding will be handled during container startup"
echo "    This ensures proper database connectivity and fresh setup."

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ Build process completed successfully!"
echo "🚀 Ready for PostgreSQL deployment with fresh migrations and seeding!"
