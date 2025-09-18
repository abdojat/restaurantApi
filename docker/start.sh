#!/usr/bin/env bash
set -e

echo "Starting Laravel application..."

# Ensure disk-backed folders exist
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/{cache,sessions,views}
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/database

# Database setup (PostgreSQL)
echo "Setting up PostgreSQL database connection..."
echo "Database host: ${DB_HOST:-localhost}"
echo "Database name: ${DB_DATABASE:-laravel}"
echo "Database user: ${DB_USERNAME:-postgres}"

# Set proper permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
chmod -R 777 /var/www/storage /var/www/bootstrap/cache || true

# Ensure log file exists and has proper permissions
touch /var/www/storage/logs/laravel.log
chown www-data:www-data /var/www/storage/logs/laravel.log
chmod 777 /var/www/storage/logs/laravel.log

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
  echo "Generating application key..."
  php artisan key:generate --force
fi

# Clear and cache configurations for production
echo "Optimizing Laravel for production..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true
php artisan clear-compiled || true

# Clear autoloader cache and regenerate
echo "Regenerating autoloader..."
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php || true
composer dump-autoload -o || true

# Run package discovery
php artisan package:discover --ansi || true

# Cache for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations and seed database
echo "Running database migrations..."
php artisan migrate --force

# Seed database only if it's empty
echo "Checking if database needs seeding..."
if [ $(php artisan tinker --execute="echo \App\Models\User::count();") -eq 0 ]; then
  echo "Seeding database with initial data..."
  php artisan db:seed --force
else
  echo "Database already has data, skipping seeding..."
fi

# Create storage link (remove existing if it exists)
echo "Creating storage link..."
rm -f /var/www/public/storage || true
php artisan storage:link || true

echo "Laravel application is ready!"

# Final database connection check before starting web server
echo "Testing database connection..."
php artisan tinker --execute="echo 'Database connection: ' . (DB::connection()->getPdo() ? 'OK' : 'FAILED');" || echo "Database connection test failed"

echo "Starting web server..."

# Start supervisor (runs php-fpm + nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf
