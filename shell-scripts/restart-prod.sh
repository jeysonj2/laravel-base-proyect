#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run the stop script to ensure all containers are stopped
./shell-scripts/stop-prod.sh

# Run the start script to start the containers again
./shell-scripts/start-prod.sh
