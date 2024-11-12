#!/bin/bash
set -e

echo "Going into root mode to delete some docker volumes"
sudo echo "Root mode: OK"
docker compose down
sleep 5
sudo rm -Rf data/*
sleep 4
docker compose up -d --build --force-recreate mongo-projection
sleep 5
