#!/bin/bash
set -e

docker run --rm -v $(pwd):/app composer:2.2.24 install --ignore-platform-reqs
