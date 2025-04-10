#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Start the containers in detached mode
docker compose -f docker-compose.yml up -d
