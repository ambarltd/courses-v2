#!/bin/bash
set -e

php vendor/bin/phpstan analyse --level max -c phpstan.src.neon -vvv
