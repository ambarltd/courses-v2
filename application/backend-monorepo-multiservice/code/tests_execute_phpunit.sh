#!/bin/bash
set -e

cd development-environment
# Allows you to do filters as needed
docker exec -it php-all-services-test "php" "vendor/bin/phpunit" "$@"