# Rust Service Integration

This project includes a Rust service that runs alongside the Laravel application, providing high-performance processing capabilities.

## Architecture

The Rust service is included by default in `docker-compose.yml` and provides:
- High-performance API endpoints for compute-intensive tasks
- Direct database access using the same credentials from Laravel's `.env`
- Redis integration for async job processing
- CORS-enabled HTTP API on port 3000

## Available Endpoints

The Rust service provides the following REST API endpoints:

- `GET /health` - Health check endpoint
- `POST /api/process` - Generic task processing
- `POST /api/score` - Calculate exam scores
- `POST /api/validate` - Validate exam structure

## Quick Start

```bash
# Start all services including Rust
docker-compose up -d

# Test the Rust service
./test-rust-service.sh

# View Rust service logs
docker-compose logs -f rust-service
```

## PHP Integration

Use the `App\Services\RustService` class to interact with the Rust service:

```php
$rustService = app(\App\Services\RustService::class);

// Health check
$health = $rustService->health();

// Calculate score
$score = $rustService->calculateScore($examId, $answers);

// Validate exam
$validation = $rustService->validateExam($examId, $questions);
```

## Environment Variables

The Rust service uses the following environment variables from Laravel's `.env`:

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - PostgreSQL connection
- `RUST_SERVICE_PORT` - Port for Rust service (default: 3000)
- `RUST_LOG` - Logging level (default: info)
- `RUST_SERVICE_URL` - URL for PHP to connect to Rust (default: http://rust-service:3000)

## Development

To modify the Rust service:

1. Edit files in `rust-service/src/`
2. Rebuild the container: `docker-compose build rust-service`
3. Restart the service: `docker-compose up -d rust-service`

The Rust service is configured for optimal performance with:
- Release mode optimizations
- Link-time optimization (LTO)
- Single codegen unit for maximum optimization