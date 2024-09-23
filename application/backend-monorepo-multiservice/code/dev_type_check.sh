#!/bin/bash
set -e

cd development-environment
echo "PHPStan for /srv/src"
docker exec -it php-all-services-test /srv/vendor/bin/phpstan analyse /srv/src --level max --no-interaction -c phpstan.src.neon --verbose
echo "PHPStan for /srv/tests"
docker exec -it php-all-services-test /srv/vendor/bin/phpstan analyse /srv/tests --level max --no-interaction -c phpstan.tests.neon --verbose