#!/bin/bash
set -e

# Allows you to do filters as needed
docker exec -it php-all-services-test "php" "vendor/bin/phpunit" "$@"

echo "All tests passed!!!"