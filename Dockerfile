# Optimized Dockerfile for faster Render deployment
FROM php:8.3-fpm-alpine

# Install system dependencies in one layer
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    tzdata \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite-dev \
    postgresql-dev \
    git \
    curl \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql bcmath mbstring zip intl opcache

# PHP configuration
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.validate_timestamps=0"; \
  echo "opcache.jit=1255"; \
  echo "opcache.jit_buffer_size=64M"; \
  echo "opcache.memory_consumption=128"; \
  echo "opcache.max_accelerated_files=10000"; \
  echo "memory_limit=256M"; \
  echo "max_execution_time=120"; \
  echo "upload_max_filesize=20M"; \
  echo "post_max_size=20M"; \
} > /usr/local/etc/php/conf.d/production.ini

WORKDIR /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install composer dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application code
COPY . .

# Run composer scripts and optimize
RUN composer dump-autoload -o \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Create directories and set permissions
RUN mkdir -p /var/www/storage/app/public \
    /var/www/storage/framework/{cache,sessions,views} \
    /var/www/storage/logs \
    /var/www/storage/database \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80
CMD ["/start.sh"]
