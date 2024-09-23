#!/bin/bash
set -e

docker exec -it php-all-services-test "php" "vendor/bin/php-cs-fixer" "fix" "--config=.php-cs-fixer.php-highest.php" "--allow-risky=yes"