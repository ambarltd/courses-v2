#!/bin/bash
set -e

echo "Going into root mode to delete docker volumes"
sudo echo "Root mode: OK"
docker compose down

# Ambar won't have to be recreated from scratch once we fix a bug.
# For now recreating from scratch means that all events are re-sent upon startup.
sleep 4
sudo rm -Rf data/ambar-emulator/
sleep 4

docker compose up -d --build --force-recreate
sleep 5

echo "The application is up!"
echo "You can navigate to localhost:8080 to view your application."
echo "If it's your first time, try signing up, verifying your email, and signing in."
echo "After you've tried this, explore the code using what you learned in Ambar's course!"