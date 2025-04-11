#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Load environment variables if .env file exists
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

# Set the appropriate compose files based on HTTP_ONLY
if [ "${HTTP_ONLY:-no}" = "yes" ]; then
  echo "Building application in HTTP-only mode..."
  docker compose -f docker-compose.prod.yml build
else
  echo "Building application with HTTPS support..."
  docker compose -f docker-compose.prod.with-https.yml build
fi
