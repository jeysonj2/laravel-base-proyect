#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Generate the Swagger documentation
docker-compose exec app php artisan l5-swagger:generate
