#!/bin/bash
set -e

echo "Running linter fixes"
docker exec -t php-all-services-test "php" "vendor/bin/php-cs-fixer" "fix" "--config=.php-cs-fixer.php-highest.php" "--allow-risky=yes"