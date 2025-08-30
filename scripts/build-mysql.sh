#!/bin/bash

echo "Building MySQL client Docker image..."
docker build -f Dockerfile.mysql -t mysql-client:latest .

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Build successful!"
    echo ""
    echo "To run the container, use ./run-mysql.sh"
else
    echo ""
    echo "✗ Build failed!"
    exit 1
fi