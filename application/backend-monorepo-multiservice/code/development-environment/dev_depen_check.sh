#!/bin/bash
set -e

echo "Checking dependency tracking for general dependencies"
docker exec -it php-all-services-test "php" "vendor/bin/deptrac" "analyze" "--config-file" "depfile_general.yml"

echo "Checking dependency tracking for bounded contexts"
docker exec -it php-all-services-test "php" "vendor/bin/deptrac" "analyze" "--config-file" "depfile_bounded_context.yml"

echo "All dependency checks succesful!!!"