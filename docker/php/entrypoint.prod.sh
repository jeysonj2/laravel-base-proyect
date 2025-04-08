#!/bin/sh
set -e

# Ensure storage directories have correct permissions
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ "$1" = "php-fpm" ]; then
  echo "Optimizing for production..."

  # Run migrations
  php artisan migrate --force

  # Optimize configuration for production
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  # Ensure keys exist
  php artisan key:generate --force
  php artisan jwt:secret --force

  # Generate Swagger documentation with production URL
  echo "Generating Swagger documentation..."
  php artisan l5-swagger:generate

  # Set appropriate permissions for storage and cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
fi

# Execute the original command
exec "$@"
