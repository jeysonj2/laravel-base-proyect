#!/bin/sh
set -e

# Ensure HTTP_PORT is set with a default value
HTTP_PORT=${HTTP_PORT:-80}
echo "Configuring Nginx to listen on HTTP port: $HTTP_PORT"

# Create temporary configuration files with expanded variables
cat /etc/nginx/conf.d/templates/http.conf | sed "s/\${HTTP_PORT}/$HTTP_PORT/g" | sed "s/\${APP_DOMAIN}/${APP_DOMAIN}/g" >/etc/nginx/conf.d/default.conf

# Check HTTP_ONLY mode
HTTP_ONLY=${HTTP_ONLY:-no}
if [ "$HTTP_ONLY" = "yes" ]; then
  echo "HTTP_ONLY mode enabled. Running with HTTP only, ignoring HTTPS configuration."
else
  # Check if SSL certificates exist
  if [ -f "/etc/letsencrypt/live/${APP_DOMAIN}/fullchain.pem" ] && [ -f "/etc/letsencrypt/live/${APP_DOMAIN}/privkey.pem" ]; then
    echo "SSL certificates found! Enabling HTTPS configuration."
    cat /etc/nginx/conf.d/templates/https.conf | sed "s/\${APP_DOMAIN}/${APP_DOMAIN}/g" >/etc/nginx/conf.d/https.conf
    # Add redirect from HTTP to HTTPS in the default.conf
    sed -i 's/# Root directory and index files/# Redirect HTTP to HTTPS\n    location \/ {\n        return 301 https:\/\/$host$request_uri;\n    }\n\n    # Root directory and index files/' /etc/nginx/conf.d/default.conf
  else
    echo "SSL certificates not found. Running with HTTP only."
  fi
fi

# Output the final configuration for debugging
echo "-------- Generated Nginx configuration --------"
cat /etc/nginx/conf.d/default.conf
echo "----------------------------------------------"

# Start Nginx
exec nginx -g 'daemon off;'
