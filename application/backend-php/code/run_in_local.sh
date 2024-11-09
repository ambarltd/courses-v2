#!/bin/bash
set -e

php bin/console galeas:dbs:updates
php bin/console galeas:define_credit_card_products

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
