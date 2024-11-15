#!/bin/bash
set -e

docker compose down
docker compose up -d --build --force-recreate

echo "The application is up!"
echo "You can navigate to localhost:8080 to view your application."
echo "You will receive further instructions in the top menu."