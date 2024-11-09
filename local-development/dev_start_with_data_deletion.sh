#!/bin/bash
set -e

echo "Going into root mode to delete some docker volumes"
sudo echo "Root mode: OK"
docker compose down
sudo rm -Rf data
docker compose up -d --build --force-recreate
sleep 5
