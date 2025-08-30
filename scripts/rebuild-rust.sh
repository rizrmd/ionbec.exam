#!/bin/bash

echo "🔨 Rebuilding Rust scoring service..."
echo "======================================"

# Stop the rust service
echo "Stopping Rust service..."
docker-compose stop rust-service

# Rebuild the rust service
echo "Building Rust service..."
docker-compose build rust-service

# Start the rust service
echo "Starting Rust service..."
docker-compose up -d rust-service

# Wait for service to be ready
echo "Waiting for service to be ready..."
sleep 5

# Check health
echo "Checking service health..."
curl -s http://localhost:3000/health | jq '.' || echo "Health check failed"

echo ""
echo "✅ Rust service rebuild complete!"
echo ""
echo "To test the service, run:"
echo "  php test-rust-scoring.php"
echo ""
echo "To view logs:"
echo "  docker-compose logs -f rust-service"