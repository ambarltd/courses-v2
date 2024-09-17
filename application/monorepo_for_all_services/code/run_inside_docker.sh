#!/bin/bash
set -e

php bin/console galeas:dbs:updates
php -S 0.0.0.0:8080 -t public