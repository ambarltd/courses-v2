#!/bin/bash
set -e

cd ../../

echo "Going into root mode to delete some docker volumes"
sudo echo "Root mode: OK"
docker compose down
sudo rm -Rf data/*
docker compose up -d --build --force-recreate



all_services_fully_healthy() {
    ! docker compose ps --format "table {{.ID}}\t{{.Name}}\t{{.Status}}" | grep -q -E "(unhealthy|starting)"
}

while ! all_services_fully_healthy; do
    echo "Waiting for all services to be healthy..."
    docker compose ps --format "table {{.ID}}\t{{.Name}}\t{{.Status}}"
    echo ""
    sleep 5
done

docker compose ps --format "table {{.ID}}\t{{.Name}}\t{{.Status}}"

echo ""
echo "=================================================================="
echo "||                   All services are healthy!                  ||"
echo "=================================================================="
echo ""

echo "=================================================================="
echo "|| You can navigate to localhost:8080 to view your application. ||"
echo "=================================================================="
echo "||    You will receive further instructions in the top menu.    ||"
echo "=================================================================="
