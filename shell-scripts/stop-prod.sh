#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Load environment variables if .env file exists
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

# Check if HTTP_ONLY is set to yes
if [ "${HTTP_ONLY:-no}" = "yes" ]; then
  echo "Stopping application in HTTP-only mode..."
  docker compose -f docker-compose.prod.yml down --remove-orphans
else
  echo "Stopping application with HTTPS support..."
  docker compose -f docker-compose.prod.with-https.yml down --remove-orphans
fi

echo "All services stopped."
