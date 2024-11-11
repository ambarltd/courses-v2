#!/bin/bash
set -e

echo "Running startup scripts for run_in_local.sh"
php bin/console galeas:dbs:updates
php bin/console galeas:define_credit_card_products
echo "Finished initial scripts for run_in_local.sh. Proceeding to start supervisor"
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
