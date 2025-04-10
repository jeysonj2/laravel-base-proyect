#!/bin/sh
set -e

# Ensure storage directories have correct permissions
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ "$1" = "php-fpm" ]; then
  echo "Optimizing for production..."

  # Explicitly remove cached PHP files to prevent environment conflicts
  echo "Removing cached PHP files..."
  rm -f /var/www/html/bootstrap/cache/*.php

  # Run migrations
  php artisan migrate --force

  # Create default superadmin user for production
  echo "Ensuring superadmin user exists..."
  php artisan app:create-default-superadmin --email="${SUPER_ADMIN_EMAIL:-superadmin_laravel_base_project@mailinator.com}" --password="${SUPER_ADMIN_PASSWORD:-}" || true

  # Ensure keys exist first - before caching configuration
  echo "Generating application keys..."
  php artisan key:generate --force
  php artisan jwt:secret --force

  # Clear any existing configuration cache
  php artisan config:clear

  # Optimize configuration for production
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  # Generate Swagger documentation with production URL
  echo "Generating Swagger documentation..."
  php artisan l5-swagger:generate

  # Set appropriate permissions for storage and cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
fi

# Execute the original command
exec "$@"
