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
# Note: Composer autoloader was already optimized during Docker build

# Run package discovery
php artisan package:discover --ansi || true

# Cache for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Database initialization
echo "Initializing PostgreSQL database..."

# Wait for database connection
echo "Waiting for PostgreSQL database to be ready..."
max_attempts=30
attempt=1
while [ $attempt -le $max_attempts ]; do
    if php artisan migrate:status >/dev/null 2>&1; then
        echo "Database connection established!"
        break
    fi
    echo "Attempt $attempt/$max_attempts - waiting for database..."
    sleep 2
    attempt=$((attempt + 1))
    if [ $attempt -gt $max_attempts ]; then
        echo "Failed to connect to database after $max_attempts attempts"
        exit 1
    fi
done

# Run migrations and seeding
echo "Running database migrations and seeding..."
php artisan migrate:fresh --force --seed

# Post-database Laravel optimizations
echo "Applying Laravel production optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Laravel optimization completed!"

# Create storage link (remove existing if it exists)
echo "Creating storage link..."
rm -f /var/www/public/storage || true
php artisan storage:link || true

echo "Laravel application is ready!"

# Final database and application readiness check
echo "Performing final application readiness check..."
echo "Testing database connection..."
if php artisan migrate:status >/dev/null 2>&1; then
    echo "Database Status: Connected"
    echo "Application Status: READY"
else
    echo "Database Status: FAILED"
    exit 1
fi

echo "Starting web server..."

# Start supervisor (runs php-fpm + nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf
