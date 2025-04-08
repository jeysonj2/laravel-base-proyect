#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Remove all containers and volumes
docker-compose -f docker-compose.prod.yml down --volumes --remove-orphans
docker-compose -f docker-compose.prod.yml rm -f
docker volume prune -f

# Run the build script to ensure all containers are built
docker-compose -f docker-compose.prod.yml build --force-rm --no-cache

# Run the start script to start the containers again
./shell-scripts/start-prod.sh
