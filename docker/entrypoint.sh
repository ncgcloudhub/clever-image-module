#!/bin/sh
set -e

# Ensure required directories exist
mkdir -p /var/www/storage/framework/cache \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

# Fix permissions so PHP-FPM (www-data) can write
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

exec "$@"
