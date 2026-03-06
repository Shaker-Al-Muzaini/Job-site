#!/bin/sh

# if .env doesn't exist, create it from example
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ensure key is generated
php artisan key:generate --no-interaction --force

# Wait for DB (briefly)
sleep 5

# Run migrations
php artisan migrate --force

# Clear and optimize application cache
php artisan optimize:clear
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link

# Use Render's PORT or default to 80
PORT=${PORT:-80}
echo "Starting Nginx on port $PORT..."

# Update Nginx config to listen on the correct PORT
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-enabled/default

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;"
