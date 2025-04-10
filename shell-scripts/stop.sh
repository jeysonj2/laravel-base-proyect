#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Stop and remove the containers
docker compose -f docker-compose.yml down --remove-orphans
