#!/usr/bin/env bash
set -o errexit

echo "🏪 Shami Restaurant - Optimized Build Process"
echo "=============================================="

# Quick dependency check
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Installing..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install dependencies with optimizations
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Create directories
echo "📁 Creating directories..."
mkdir -p storage/{app/public,framework/{cache,sessions,views},logs,database} bootstrap/cache

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache

# Generate key if needed
if [ -z "$APP_KEY" ]; then
  echo "🔑 Generating application key..."
  php artisan key:generate --force
fi

# Optimize for production (skip if Docker will handle it)
if [ "$SKIP_OPTIMIZATION" != "true" ]; then
  echo "⚡ Optimizing Laravel..."
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ Build completed successfully!"
