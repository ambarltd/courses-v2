#!/bin/bash
set -e

echo "Running tests"

docker  exec -t backend-php "find" "/symfony_tmp/" "-type" "f" "-delete"
# Allows you to do filters as needed
docker exec -t backend-php "php" "vendor/bin/phpunit" "$@"

echo "All tests passed!!!"