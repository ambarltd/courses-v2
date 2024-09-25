#!/bin/bash
set -e

cd ../
docker run --rm -v $(pwd):/app composer:2.2.24 install --ignore-platform-reqs
cd development-environment
docker compose down
docker compose up -d --build --force-recreate
echo "Waiting for containers to start"
sleep 5
docker ps


if [ "$(docker ps -q -f name=php-all-services-test)" ]; then
    echo "The container 'php-all-services-test' is up and running."
    docker logs php-all-services-test | tail -n 5
else
    echo "The container 'php-all-services-test' is not running."
    docker logs php-all-services-test | tail -n 500
fi