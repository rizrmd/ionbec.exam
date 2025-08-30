#!/bin/bash

echo "Building Laravel Docker image..."
docker build -f Dockerfile.laravel -t laravel-app:latest .

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Build successful!"
    echo ""
    echo "To run the container, use ./run-laravel.sh"
else
    echo ""
    echo "✗ Build failed!"
    exit 1
fi