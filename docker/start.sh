#!/bin/sh

# Start PHP-FPM
php-fpm -D

# Wait for PHP-FPM to start
sleep 2

# Run database migrations if needed
if [ -f /var/www/html/database/database.sqlite ]; then
    echo "Running database migrations..."
    php /var/www/html/artisan migrate --force
fi

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;" 