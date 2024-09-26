#!/bin/bash
set -e

echo "Checking dependency tracking for general dependencies"
docker exec -t php-all-services-test "php" "-d" "memory_limit=256M" "vendor/bin/deptrac" "analyze" "--config-file" "depfile_general.yml"

echo "Checking dependency tracking for bounded contexts"
docker exec -t php-all-services-test "php" "-d" "memory_limit=256M" "vendor/bin/deptrac" "analyze" "--config-file" "depfile_bounded_context.yml"

echo "All dependency checks succesful!!!"