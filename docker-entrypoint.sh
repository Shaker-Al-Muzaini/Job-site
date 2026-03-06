#!/bin/sh
set -e

# Link storage (only once)
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Use Render's PORT or default to 80
PORT=${PORT:-80}
echo "Configuring Nginx to port $PORT..."

# Replace port 80 in Nginx config with the dynamic PORT from Render
# Using a simple sed that matches 'listen 80' variants
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-enabled/default

# Clear some cache that might be stale in a new container
php artisan config:clear || true
php artisan view:clear || true

# Run migrations (Free tier work-around)
php artisan migrate --force

echo "Starting services..."

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
