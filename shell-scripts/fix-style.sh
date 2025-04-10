#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run the fix-style command
docker compose exec app composer fix-style
