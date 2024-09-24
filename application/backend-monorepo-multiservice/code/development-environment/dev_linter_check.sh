#!/bin/bash
set -e

echo "Running linter checks"

if docker exec -t php-all-services-test "php" "vendor/bin/php-cs-fixer" "fix" "--config=.php-cs-fixer.php-highest.php" "--allow-risky=yes" "--dry-run" | grep 'src'; then
    echo "Styling in ./src failed. To fix run ./dev_linter_fix.sh"
    exit 1
else
    echo "Styling in ./src passed."
fi
if docker exec -t php-all-services-test "php" "vendor/bin/php-cs-fixer" "fix" "--config=.php-cs-fixer.php-highest.php" "--allow-risky=yes" "--dry-run" | grep 'tests'; then
    echo "Styling in ./tests failed. To fix run ./dev_linter_fix.sh"
    exit 1
else
    echo "Styling in ./tests passed."
fi

echo "All linter checks succesful!!!"
