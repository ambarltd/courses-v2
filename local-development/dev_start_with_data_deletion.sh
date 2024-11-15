#!/bin/bash
set -e

echo "Going into root mode to delete some docker volumes"
sudo echo "Root mode: OK"
docker compose down
sudo rm -Rf data/*
docker compose up -d --build --force-recreate
echo "The application is up!"
echo "You can navigate to localhost:8080 to view your application."
echo "You will receive further instructions in the top menu."