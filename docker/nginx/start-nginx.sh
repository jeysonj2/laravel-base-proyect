#!/bin/sh
set -e

# Process environment variables in configuration files
envsubst '${APP_DOMAIN}' </etc/nginx/conf.d/templates/http.conf >/etc/nginx/conf.d/default.conf

# Check if SSL certificates exist
if [ -f "/etc/letsencrypt/live/${APP_DOMAIN}/fullchain.pem" ] && [ -f "/etc/letsencrypt/live/${APP_DOMAIN}/privkey.pem" ]; then
  echo "SSL certificates found! Enabling HTTPS configuration."
  envsubst '${APP_DOMAIN}' </etc/nginx/conf.d/templates/https.conf >/etc/nginx/conf.d/https.conf
  # Add redirect from HTTP to HTTPS in the default.conf
  sed -i 's/# Root directory and index files/# Redirect HTTP to HTTPS\n    location \/ {\n        return 301 https:\/\/$host$request_uri;\n    }\n\n    # Root directory and index files/' /etc/nginx/conf.d/default.conf
else
  echo "SSL certificates not found. Running with HTTP only."
fi

# Start Nginx
exec nginx -g 'daemon off;'
