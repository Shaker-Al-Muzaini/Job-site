#!/bin/sh
set -e

# Ensure storage directories exist
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache \
         storage/logs \
         bootstrap/cache

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Link storage
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Use Render's PORT or default to 80
PORT=${PORT:-80}
echo "Configuring Nginx to port $PORT..."

# Replace port in Nginx config
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-enabled/default

# Clear cache
php artisan config:clear || true
php artisan view:clear || true
php artisan route:clear || true

# Run migrations
php artisan migrate --force || true

echo "Starting services..."

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
