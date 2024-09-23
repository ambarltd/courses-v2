#!/bin/bash
set -e

php bin/console galeas:dbs:updates

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
