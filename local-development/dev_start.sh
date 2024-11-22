#!/bin/bash
set -e

docker compose down
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

echo "All services are healthy!"
echo ""
echo "============================================================"
echo "============================================================"
echo "============================================================"
echo "============================================================"
echo "The application is up!"
echo "============================================================"
echo "You can navigate to localhost:8080 to view your application."
echo "============================================================"
echo "You will receive further instructions in the top menu."
echo "============================================================"
echo "============================================================"
echo "============================================================"
echo "============================================================"
echo "============================================================"