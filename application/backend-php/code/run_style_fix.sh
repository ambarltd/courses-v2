#!/bin/bash
set -e

php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php-highest.php --allow-risky=yes
