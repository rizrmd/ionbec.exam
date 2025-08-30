#!/bin/bash

echo "Testing Rust availability in Docker container"
echo "============================================="

# Test if Rust is available in the octane container
docker-compose exec octane bash -c "rustc --version && cargo --version" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Rust is available in the Docker container"
else
    echo "✗ Rust is not available. Rebuilding container..."
    docker-compose build octane
    docker-compose up -d octane
    echo "Testing again..."
    docker-compose exec octane bash -c "rustc --version && cargo --version"
fi