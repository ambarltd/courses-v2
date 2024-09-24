#!/bin/bash
set -e

echo "Running tests"

# Allows you to do filters as needed
docker exec -t php-all-services-test "php" "vendor/bin/phpunit" "$@"

echo "All tests passed!!!"