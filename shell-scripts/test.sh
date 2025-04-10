#!/bin/bash

# Go to the root of the project
cd "$(dirname "$0")/.."

# Check if at least one argument was passed
if [ $# -eq 0 ]; then
  echo "Please provide at least one test class to run."
  echo "Usage: ./shell-scripts/test.sh [TestClass1] [TestClass2] ..."
  echo "Example: ./shell-scripts/test.sh Unit\\Models\\RoleTest Feature\\Auth\\LoginTest"
  exit 1
fi

# Build the filter string for multiple test classes
FILTER=""
for testClass in "$@"; do
  # Add the test class to the filter, replacing \ with \\
  if [ -z "$FILTER" ]; then
    FILTER="${testClass//\\/\\\\}"
  else
    FILTER="$FILTER|${testClass//\\/\\\\}"
  fi
done

# Run the tests with the specified filter
echo "Running tests: $FILTER"
docker compose exec app php artisan test --filter="$FILTER"
