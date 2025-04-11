#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Generate the Swagger documentation
docker compose exec app php artisan l5-swagger:generate
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
