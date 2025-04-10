#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run the tests with coverage
docker compose exec app php artisan test --coverage
