#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run the Laravel migrations and seed the database
# This assumes that the database is already set up and running
docker compose exec app php artisan migrate --seed
