#!/bin/bash
set -e

php bin/console galeas:dbs:updates
php bin/console galeas:define_credit_card_products
php -S 0.0.0.0:8080 -t public