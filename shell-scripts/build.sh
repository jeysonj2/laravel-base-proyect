#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run build command
docker compose -f docker-compose.yml build
