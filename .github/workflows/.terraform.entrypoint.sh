#!/bin/sh

sleep 5

while ! docker info >/dev/null 2>&1; do
    echo "Docker is not ready yet, retrieving Docker daemon status..."
    # Capture and display all output that could indicate what's going wrong
    docker info 2>&1
    sleep 2
done

echo "Docker is now running."

exec terraform "$@"