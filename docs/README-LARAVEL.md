# Laravel Docker Setup with DATABASE_URL and REDIS_URL

This setup configures Laravel to work with only two environment variables: `DATABASE_URL` and `REDIS_URL`.

## Configuration

Laravel natively supports URL-based configuration for both MySQL and Redis. This setup uses:

1. **DATABASE_URL** - Complete MySQL connection string
   ```
   mysql://mysql:S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK@107.155.75.50:5654/default
   ```

2. **REDIS_URL** - Complete Redis connection string
   ```
   redis://default:43bgTxX07rGOxcDeD2Z67qc57qSAH39KEUJXCHap7W613KVNZPnLaOBdBG2Z0Yq6@107.155.75.50:9675/0
   ```

## Quick Start

### 1. Set up environment
```bash
# Copy example environment file
cp .env.example .env

# The .env file only needs:
# DATABASE_URL=mysql://...
# REDIS_URL=redis://...
```

### 2. Build the Docker image
```bash
./build-laravel.sh
```

### 3. Test database connections
```bash
# Test database connection
./run-laravel.sh test-db

# Test Redis connection
./run-laravel.sh test-redis
```

### 4. Run migrations
```bash
./run-laravel.sh migrate
```

### 5. Start the development server
```bash
# Start Laravel development server on port 8000
./run-laravel.sh serve

# Or run interactively
./run-laravel.sh bash
```

## Available Commands

Inside the container:
- `test-db` - Test MySQL database connection
- `test-redis` - Test Redis connection
- `migrate` - Run Laravel migrations
- `tinker` - Start Laravel Tinker REPL
- `serve` - Start development server

## Manual Docker Commands

```bash
# Build
docker build -f Dockerfile.laravel -t laravel-app:latest .

# Run with environment variables
docker run -it --rm \
  -e DATABASE_URL="mysql://user:pass@host:port/database" \
  -e REDIS_URL="redis://user:pass@host:port/0" \
  -p 8000:8000 \
  laravel-app:latest

# Run specific artisan command
docker run -it --rm \
  -e DATABASE_URL="..." \
  -e REDIS_URL="..." \
  laravel-app:latest \
  php artisan migrate:status
```

## How It Works

1. **Laravel's Native URL Support**: Laravel's `config/database.php` already supports `DATABASE_URL` for MySQL connections and `REDIS_URL` for Redis connections.

2. **No Additional Configuration Needed**: When these URLs are set, Laravel automatically parses them and uses the connection details. You don't need to set individual `DB_HOST`, `DB_PORT`, `DB_USERNAME`, etc.

3. **URL Format**:
   - MySQL: `mysql://username:password@host:port/database`
   - Redis: `redis://[username]:password@host:port/database_number`

## Testing the Configuration

To verify everything is working:

```bash
# 1. Check database connection
./run-laravel.sh php artisan tinker --execute="DB::connection()->getPdo()"

# 2. Check Redis connection
./run-laravel.sh php artisan tinker --execute="Redis::ping()"

# 3. View parsed configuration
./run-laravel.sh php artisan config:show database
```

## Troubleshooting

If connections fail:

1. **Verify URLs are correct**:
   ```bash
   echo $DATABASE_URL
   echo $REDIS_URL
   ```

2. **Check network connectivity**:
   ```bash
   ./run-laravel.sh bash
   # Inside container:
   mysql -h 107.155.75.50 -P 5654 -u mysql -p
   redis-cli -h 107.155.75.50 -p 9675
   ```

3. **View Laravel logs**:
   ```bash
   ./run-laravel.sh tail -f storage/logs/laravel.log
   ```