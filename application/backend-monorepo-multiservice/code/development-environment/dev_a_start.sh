#!/bin/bash
set -e

cd ../
docker run --rm -v $(pwd):/app composer:2.2.24 install --ignore-platform-reqs
cp config/routes_by_service_for_build_stage/routes_all_for_local_development.yaml config/routes.yaml
cd development-environment
docker compose down
docker compose up -d --build --force-recreate
echo "Waiting for containers to start"
sleep 30
docker ps


if [ "$(docker ps -q -f name=php-all-services-test)" ]; then
    echo "The container 'php-all-services-test' is up and running."
    docker logs php-all-services-test | tail -n 5
else
    echo "The container 'php-all-services-test' is not running."
    docker logs php-all-services-test | tail -n 500
fi

echo "If you reached this point, you have started the local environment (what you can see above = logs)."
echo "Ready to proceed with the CI/CD pipeline, or use the local environment."