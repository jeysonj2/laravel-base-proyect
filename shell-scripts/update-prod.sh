#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Fetch and pull the latest changes from the remote repository
git fetch origin
git pull

# Run the reset-prod script to ensure all containers are stopped, built, and started
./shell-scripts/reset-prod.sh
