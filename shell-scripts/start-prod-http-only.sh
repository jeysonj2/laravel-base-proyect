#!/bin/bash

echo "Starting application in HTTP-only mode (without HTTPS)"
echo "This mode is useful when port 443 is already in use on the host machine"
echo "--------------------------------------------------------------------"

# Use the HTTP-only configuration file directly, which extends docker-compose.prod.yml
echo "Starting services in HTTP-only mode..."
docker compose -f docker-compose.prod.http-only.yml up -d

echo "Services started in HTTP-only mode. Only port ${HTTP_PORT:-80} is mapped."
