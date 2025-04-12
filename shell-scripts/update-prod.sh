#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Git stash to save any uncommitted changes
git stash
if [ $? -ne 0 ]; then
  echo "Failed to stash changes. Please commit or discard your changes before running this script."
  exit 1
fi

# Fetch and pull the latest changes from the remote repository
git fetch origin
git pull

# Run the reset-prod script to ensure all containers are stopped, built, and started
./shell-scripts/reset-prod.sh
