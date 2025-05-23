#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Run the stop script to ensure all containers are stopped
./shell-scripts/stop.sh

# Run the build script to ensure all containers are built
./shell-scripts/build.sh

# Run the start script to start the containers again
./shell-scripts/start.sh
