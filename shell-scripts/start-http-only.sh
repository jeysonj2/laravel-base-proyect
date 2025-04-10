#!/bin/bash

echo "Starting application in HTTP-only mode (without HTTPS)"
echo "This mode is useful when port 443 is already in use on the host machine"
echo "--------------------------------------------------------------------"

docker compose -f docker-compose.prod.yml -f docker-compose.http-only.yml up -d
