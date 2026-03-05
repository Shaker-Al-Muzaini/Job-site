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

# Create storage link
php artisan storage:link

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
