#!/bin/bash
set -e

cd ../
docker run --rm -v $(pwd):/app composer:2.2.24 install --ignore-platform-reqs
cd development-environment
docker compose down
docker compose up -d --build --force-recreate
sleep 5