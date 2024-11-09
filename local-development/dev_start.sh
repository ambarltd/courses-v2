#!/bin/bash
set -e

echo "Going into root mode to delete some docker volumes"
sudo echo "Root mode: OK"
docker compose down
#sudo rm -f data/ambar-emulator/queues/inventory.lock
#sudo rm -f data/ambar-emulator/queues/**/*.lock
sudo rm -Rf data/ambar-emulator/
docker compose up -d --build --force-recreate
sleep 5