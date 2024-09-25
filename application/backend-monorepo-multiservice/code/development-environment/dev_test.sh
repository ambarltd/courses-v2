#!/bin/bash
set -e

echo "Running tests"

docker  exec -t php-all-services-test "find" "/symfony_tmp/" "-type" "f" "-delete"
# Allows you to do filters as needed
docker exec -t php-all-services-test "php" "vendor/bin/phpunit" "$@"

echo "All tests passed!!!"