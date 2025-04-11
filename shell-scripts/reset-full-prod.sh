#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Load environment variables if .env file exists
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

# Set the appropriate compose files based on HTTP_ONLY
if [ "${HTTP_ONLY:-no}" = "yes" ]; then
  COMPOSE_FILES="-f docker-compose.prod.yml"
  echo "Resetting application in HTTP-only mode..."
else
  COMPOSE_FILES="-f docker-compose.prod.with-https.yml"
  echo "Resetting application with HTTPS support..."
fi

# Remove all containers and volumes
docker compose ${COMPOSE_FILES} down --volumes --remove-orphans
docker compose ${COMPOSE_FILES} rm -f
docker volume prune -f

# Run the build script to ensure all containers are built
docker compose ${COMPOSE_FILES} build --force-rm --no-cache

# Run the start script to start the containers again
./shell-scripts/start-prod.sh
