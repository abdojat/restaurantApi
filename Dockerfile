# --- 1) Build PHP vendor
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction
COPY . .
RUN composer dump-autoload -o

# --- 2) Runtime: PHP-FPM + Nginx + Supervisor
FROM php:8.3-fpm-alpine

# OS deps
RUN apk add --no-cache nginx supervisor bash tzdata icu-dev libzip-dev oniguruma-dev sqlite-dev git

# PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite bcmath mbstring zip intl opcache

# Opcache for prod
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.validate_timestamps=0"; \
  echo "opcache.jit=1255"; \
  echo "opcache.jit_buffer_size=128M"; \
} > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www

# App code
COPY --from=vendor /app /var/www

# Nginx + Supervisor configs + start script
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Public storage symlink (ignore errors if already exists)
RUN php artisan storage:link || true

# Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80
CMD ["/start.sh"]
