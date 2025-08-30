#!/bin/bash

# Function to parse MySQL URL
parse_mysql_url() {
    # Extract components from mysql://user:pass@host:port/database
    local url="$1"
    
    # Remove mysql:// prefix
    url="${url#mysql://}"
    
    # Extract user:pass
    local userpass="${url%%@*}"
    export DB_USERNAME="${userpass%%:*}"
    export DB_PASSWORD="${userpass#*:}"
    
    # Extract host:port/database
    local hostpart="${url#*@}"
    local hostport="${hostpart%%/*}"
    export DB_HOST="${hostport%%:*}"
    export DB_PORT="${hostport#*:}"
    export DB_DATABASE="${hostpart#*/}"
    
    # Handle cases where port is not specified (default to 3306)
    if [ "$DB_PORT" = "$DB_HOST" ]; then
        DB_PORT="3306"
    fi
}

# Function to parse Redis URL
parse_redis_url() {
    # Extract components from redis://[user:pass@]host:port[/database]
    local url="$1"
    
    # Remove redis:// prefix
    url="${url#redis://}"
    
    # Check if auth exists
    if [[ "$url" == *"@"* ]]; then
        local auth="${url%%@*}"
        export REDIS_AUTH="$auth"
        url="${url#*@}"
    fi
    
    # Extract host:port
    local hostport="${url%%/*}"
    export REDIS_HOST="${hostport%%:*}"
    export REDIS_PORT="${hostport#*:}"
    
    # Handle cases where port is not specified (default to 6379)
    if [ "$REDIS_PORT" = "$REDIS_HOST" ]; then
        REDIS_PORT="6379"
    fi
}

# Parse URLs
parse_mysql_url "$DATABASE_URL"
parse_redis_url "$REDIS_URL"

echo "========================================="
echo "Database & Cache Client Container"
echo "========================================="
echo ""
echo "MySQL Configuration:"
echo "  Host: $DB_HOST"
echo "  Port: $DB_PORT"
echo "  Database: $DB_DATABASE"
echo "  Username: $DB_USERNAME"
echo ""
echo "Redis Configuration:"
echo "  Host: $REDIS_HOST"
echo "  Port: $REDIS_PORT"
echo ""
echo "Available commands:"
echo "  test-connection  - Test MySQL and Redis connections"
echo "  mysql-connect    - Connect to MySQL interactively"
echo "  mysql-query      - Run a SQL query (pass as argument)"
echo "  redis-connect    - Connect to Redis CLI"
echo "  redis-ping       - Test Redis connection"
echo ""
echo "========================================="

# Create helper functions
cat > /usr/local/bin/test-connection << 'EOF'
#!/bin/bash
/app/test-connection.sh
EOF

cat > /usr/local/bin/mysql-connect << 'EOF'
#!/bin/bash
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
EOF

cat > /usr/local/bin/mysql-query << 'EOF'
#!/bin/bash
if [ -z "$1" ]; then
    echo "Usage: mysql-query 'SQL QUERY'"
    exit 1
fi
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "$1" "$DB_DATABASE"
EOF

cat > /usr/local/bin/redis-connect << 'EOF'
#!/bin/bash
if [ -n "$REDIS_AUTH" ]; then
    redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" -a "$REDIS_AUTH"
else
    redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT"
fi
EOF

cat > /usr/local/bin/redis-ping << 'EOF'
#!/bin/bash
echo "Testing Redis connection..."
if [ -n "$REDIS_AUTH" ]; then
    redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" -a "$REDIS_AUTH" ping
else
    redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping
fi
EOF

# Make helper scripts executable
chmod +x /usr/local/bin/test-connection
chmod +x /usr/local/bin/mysql-connect
chmod +x /usr/local/bin/mysql-query
chmod +x /usr/local/bin/redis-connect
chmod +x /usr/local/bin/redis-ping

# Update test-connection.sh to test both MySQL and Redis
cat > /app/test-connection.sh << 'EOF'
#!/bin/bash

echo "========================================="
echo "Testing MySQL connection..."
echo "Host: $DB_HOST"
echo "Port: $DB_PORT"
echo "Database: $DB_DATABASE"
echo "User: $DB_USERNAME"
echo ""

mysql -h "$DB_HOST" \
      -P "$DB_PORT" \
      -u "$DB_USERNAME" \
      -p"$DB_PASSWORD" \
      -e "SELECT VERSION() as 'MySQL Version', DATABASE() as 'Current Database', NOW() as 'Server Time';" \
      "$DB_DATABASE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ MySQL connection successful!"
    
    mysql -h "$DB_HOST" \
          -P "$DB_PORT" \
          -u "$DB_USERNAME" \
          -p"$DB_PASSWORD" \
          -e "SHOW TABLES;" \
          "$DB_DATABASE" 2>/dev/null
else
    echo ""
    echo "✗ MySQL connection failed!"
fi

echo ""
echo "========================================="
echo "Testing Redis connection..."
echo "Host: $REDIS_HOST"
echo "Port: $REDIS_PORT"
echo ""

if [ -n "$REDIS_AUTH" ]; then
    redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" -a "$REDIS_AUTH" ping > /dev/null 2>&1
else
    redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping > /dev/null 2>&1
fi

if [ $? -eq 0 ]; then
    echo "✓ Redis connection successful!"
else
    echo "✗ Redis connection failed!"
fi

echo "========================================="
EOF

chmod +x /app/test-connection.sh

# Execute the command passed to docker run
exec "$@"