# Single Dockerfile for all environments (local, dev VPS, prod VPS)
# Uses nginx + PHP-FPM in one container via supervisord

FROM php:8.2-fpm-alpine

# Build arg: set COMPOSER_DEV=1 for local dev to include dev dependencies
ARG COMPOSER_DEV=0

# Install system dependencies (nginx + supervisor for single-container setup)
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    libpng-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd

# Install Redis PHP extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# PHP-FPM listens on 127.0.0.1:9000 (nginx in same container proxies to it)
RUN sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first for better layer caching
COPY composer.json composer.lock* ./
RUN composer install $( [ "$COMPOSER_DEV" = "1" ] && echo "" || echo "--no-dev" ) \
    --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy full source
COPY . .

# Finish Composer setup
RUN composer dump-autoload --optimize

# Install Node dependencies and build frontend assets
RUN npm install && npm run build

# Ensure writable directories exist with correct permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions \
             storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Nginx + supervisor config (single container)
RUN mkdir -p /etc/nginx/http.d
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.prod.conf /etc/nginx/http.d/default.conf

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy and enable entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
