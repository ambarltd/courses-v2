#!/bin/bash
set -e

docker compose down
docker compose up -d --build --force-recreate

echo "The application is up!"
echo "You can navigate to localhost:8080 to view your application."
echo "If it's your first time, try signing up, verifying your email, and signing in."
echo "After you've tried this, explore the code using what you learned in Ambar's course!"