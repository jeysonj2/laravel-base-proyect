#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Load environment variables if .env file exists
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

# Check if HTTP_ONLY is set to yes
if [ "${HTTP_ONLY:-no}" = "yes" ]; then
  echo "Starting application in HTTP-only mode (without HTTPS)..."
  echo "This mode is useful when port 443 is already in use on the host machine"
  echo "--------------------------------------------------------------------"
  docker compose -f docker-compose.prod.yml up -d
else
  echo "Starting application with HTTPS support..."
  echo "This mode includes Let's Encrypt certificate generation"
  echo "--------------------------------------------------------------------"
  docker compose -f docker-compose.prod.with-https.yml up -d
fi

echo "Services started. HTTP port: ${HTTP_PORT:-80}"
if [ "${HTTP_ONLY:-no}" != "yes" ]; then
  echo "HTTPS port: 443"
fi
