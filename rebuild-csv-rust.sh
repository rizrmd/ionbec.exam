#!/bin/bash

echo "🔨 Rebuilding Rust service with CSV processing..."
echo "================================================"

# Stop the rust service
echo "Stopping Rust service..."
docker-compose stop rust-service

# Rebuild the rust service (force rebuild to include new dependencies)
echo "Building Rust service with CSV support..."
docker-compose build --no-cache rust-service

# Start the rust service
echo "Starting Rust service..."
docker-compose up -d rust-service

# Wait for service to be ready
echo "Waiting for service to be ready..."
sleep 10

# Check health
echo "Checking service health..."
curl -s http://localhost:3000/health | jq '.' || echo "Health check failed"

echo ""
echo "✅ Rust service with CSV processing rebuild complete!"
echo ""
echo "Available endpoints:"
echo "  - POST /api/csv/process     (single CSV file)"
echo "  - POST /api/csv/batch       (multiple CSV files)"
echo "  - POST /api/score/calculate (score calculation)"
echo "  - POST /api/score/batch     (batch scoring)"
echo ""
echo "To test CSV processing, run:"
echo "  php test-csv-processing.php"
echo ""
echo "To test overall scoring, run:"
echo "  php test-rust-scoring.php"
echo ""
echo "To view logs:"
echo "  docker-compose logs -f rust-service"