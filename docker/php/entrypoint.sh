#!/bin/bash
set -e

# Make sure storage directories have correct permissions
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Execute additional commands if necessary
if [ "$1" = "php-fpm" ]; then
    echo "Applying permissions for Laravel..."
    if [ ! -d "/var/www/html/vendor" ]; then
        echo "Installing dependencies..."
        composer install --no-interaction --no-plugins --no-scripts
    fi
    
    # Run migrations before cleaning the cache
    echo "Running migrations..."
    php artisan migrate --force
    
    # Clear cache
    echo "Clearing cache..."
    php artisan optimize:clear
    
    # Generate key if it doesn't exist
    php artisan key:generate --no-interaction --force
fi

# Execute the original command
exec "$@"
