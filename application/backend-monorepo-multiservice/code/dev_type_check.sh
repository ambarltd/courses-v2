#!/bin/bash
set -e

cd development-environment
echo "PHPStan for /srv/src"
docker exec -it php-all-services-test "php" "vendor/bin/phpstan" "analyse" "--level" "max" "-c" "phpstan.src.neon" "-vvv"