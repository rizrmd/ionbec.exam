# Database & Cache Docker Client

This setup provides a Docker-based client for MySQL and Redis connections using only two environment variables.

## Configuration

### Environment Variables

Set only two environment variables:

1. **DATABASE_URL** - MySQL connection string
   ```
   mysql://username:password@host:port/database
   ```

2. **REDIS_URL** - Redis connection string
   ```
   redis://[auth@]host:port
   ```

### Setting Environment Variables

**Option 1: Using .env file** (recommended)
```bash
# Copy the example file
cp .env.example .env

# Edit .env with your values:
DATABASE_URL=mysql://mysql:password@107.155.75.50:5654/default
REDIS_URL=redis://localhost:6379
```

**Option 2: Export in shell**
```bash
export DATABASE_URL="mysql://mysql:S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK@107.155.75.50:5654/default"
export REDIS_URL="redis://localhost:6379"
```

## Quick Start

### 1. Build the Docker image:
```bash
./build-mysql.sh
```

### 2. Run the container:
```bash
# Interactive shell
./run-mysql.sh

# Test both connections
./run-mysql.sh test-connection

# MySQL commands
./run-mysql.sh mysql-connect
./run-mysql.sh mysql-query "SHOW TABLES;"

# Redis commands
./run-mysql.sh redis-connect
./run-mysql.sh redis-ping
```

## Manual Docker Commands

### Build:
```bash
docker build -f Dockerfile.mysql -t mysql-client:latest .
```

### Run with environment variables:
```bash
docker run -it --rm \
  -e DATABASE_URL="mysql://user:pass@host:port/database" \
  -e REDIS_URL="redis://host:port" \
  mysql-client:latest
```

## Available Commands

Inside the container, you have access to:

**MySQL Commands:**
- `test-connection` - Test both MySQL and Redis connections
- `mysql-connect` - Open MySQL interactive CLI
- `mysql-query 'SQL'` - Execute a SQL query

**Redis Commands:**
- `redis-connect` - Open Redis CLI
- `redis-ping` - Test Redis connectivity

## Laravel Integration

To use this database with your Laravel application, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=107.155.75.50
DB_PORT=5654
DB_DATABASE=default
DB_USERNAME=mysql
DB_PASSWORD=S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK
```

Or use the DATABASE_URL format:
```env
DATABASE_URL=mysql://mysql:S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK@107.155.75.50:5654/default
```

## Security Notes

- The `.env.mysql` file contains sensitive credentials - keep it secure
- Add `.env.mysql` to your `.gitignore` file
- Use environment variables or secrets management in production