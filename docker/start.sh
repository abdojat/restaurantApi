#!/usr/bin/env bash
set -e

echo "Starting Laravel application..."

# Ensure disk-backed folders exist
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/{cache,sessions,views}
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/database

# Ensure SQLite DB file exists and has proper permissions
echo "Setting up SQLite database..."

# Remove existing database file if it has wrong permissions
if [ -f /var/www/storage/database/database.sqlite ]; then
  echo "Removing existing database file to fix permissions..."
  rm -f /var/www/storage/database/database.sqlite
fi

# Create database file with proper permissions
echo "Creating new SQLite database with correct permissions..."
touch /var/www/storage/database/database.sqlite

# Set proper permissions for SQLite database and directory
echo "Setting database permissions..."
chmod 755 /var/www/storage/database
chmod 666 /var/www/storage/database/database.sqlite
chown www-data:www-data /var/www/storage/database/database.sqlite

# Verify database is writable
echo "Verifying database permissions..."
ls -la /var/www/storage/database/
if [ -w /var/www/storage/database/database.sqlite ]; then
  echo "Database is writable ✓"
else
  echo "Database is NOT writable ✗"
  echo "Attempting to fix permissions again..."
  chmod 666 /var/www/storage/database/database.sqlite
  chown www-data:www-data /var/www/storage/database/database.sqlite
fi

# Set proper permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true

# Ensure log file exists and has proper permissions
touch /var/www/storage/logs/laravel.log
chown www-data:www-data /var/www/storage/logs/laravel.log
chmod 664 /var/www/storage/logs/laravel.log

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

# Verify database is still writable after migrations
echo "Verifying database permissions after migrations..."
ls -la /var/www/storage/database/
if [ -w /var/www/storage/database/database.sqlite ]; then
  echo "Database is still writable after migrations ✓"
else
  echo "Database became read-only after migrations ✗"
  echo "Fixing permissions again..."
  chmod 666 /var/www/storage/database/database.sqlite
  chown www-data:www-data /var/www/storage/database/database.sqlite
fi

# Seed database only if it's empty
if [ ! -s /var/www/storage/database/database.sqlite ] || [ $(php artisan tinker --execute="echo \App\Models\User::count();") -eq 0 ]; then
  echo "Seeding database with initial data..."
  php artisan db:seed --force
fi

# Create storage link
php artisan storage:link || true

echo "Laravel application is ready!"

# Final database permission check before starting web server
echo "Final database permission check..."
ls -la /var/www/storage/database/
if [ -w /var/www/storage/database/database.sqlite ]; then
  echo "Database is writable - ready to start web server ✓"
else
  echo "Database is NOT writable - attempting final fix..."
  chmod 666 /var/www/storage/database/database.sqlite
  chown www-data:www-data /var/www/storage/database/database.sqlite
  ls -la /var/www/storage/database/
fi

echo "Starting web server..."

# Start supervisor (runs php-fpm + nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf
