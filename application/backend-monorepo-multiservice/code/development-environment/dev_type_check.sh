#!/bin/bash
set -e

echo "PHPStan for /srv/src"
docker exec -it php-all-services-test "php" "vendor/bin/phpstan" "analyse" "--level" "max" "-c" "phpstan.src.neon" "-vvv"

echo "All type checks succesful!!!"