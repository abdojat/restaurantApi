# --- 1) Build PHP vendor
FROM composer:2 AS vendor
WORKDIR /app

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies without running post-install scripts that require Laravel
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts

# Copy application code
COPY . .

# Clear any existing autoloader cache and regenerate
RUN rm -f vendor/composer/autoload_classmap.php vendor/composer/autoload_static.php || true
RUN composer dump-autoload -o

# --- 2) Runtime: PHP-FPM + Nginx + Supervisor
FROM php:8.3-fpm-alpine

# OS deps
RUN apk add --no-cache nginx supervisor bash tzdata icu-dev libzip-dev oniguruma-dev sqlite-dev git curl

# PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite bcmath mbstring zip intl opcache

# Opcache for production
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.validate_timestamps=0"; \
  echo "opcache.jit=1255"; \
  echo "opcache.jit_buffer_size=128M"; \
  echo "opcache.memory_consumption=256"; \
  echo "opcache.max_accelerated_files=20000"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# PHP configuration for production
RUN { \
  echo "memory_limit=512M"; \
  echo "max_execution_time=300"; \
  echo "upload_max_filesize=50M"; \
  echo "post_max_size=50M"; \
} > /usr/local/etc/php/conf.d/production.ini

WORKDIR /var/www

# App code
COPY --from=vendor /app /var/www

# Nginx + Supervisor configs + start script
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Create necessary directories
RUN mkdir -p /var/www/storage/app/public \
    /var/www/storage/framework/{cache,sessions,views} \
    /var/www/storage/logs \
    /var/www/storage/database \
    /var/www/bootstrap/cache

# Public storage symlink (ignore errors if already exists)
RUN php artisan storage:link || true

# Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    touch /var/www/storage/logs/laravel.log && \
    chown www-data:www-data /var/www/storage/logs/laravel.log && \
    chmod 664 /var/www/storage/logs/laravel.log

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
  CMD curl -f http://localhost/api/menu/recommendations || exit 1

EXPOSE 80
CMD ["/start.sh"]
