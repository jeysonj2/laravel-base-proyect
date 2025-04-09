#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run the tests
docker-compose exec app php artisan test
