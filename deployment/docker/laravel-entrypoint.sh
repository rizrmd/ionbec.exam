#!/bin/bash

# Generate Laravel application key if not set
if [ -z "$APP_KEY" ] && [ ! -f /app/.env ]; then
    echo "Generating Laravel application key..."
    php artisan key:generate
fi

echo "========================================="
echo "Laravel Application Container"
echo "========================================="
echo ""
echo "Configuration:"
echo "  DATABASE_URL: ${DATABASE_URL:0:50}..."
echo "  REDIS_URL: ${REDIS_URL:0:50}..."
echo ""
echo "Available commands:"
echo "  test-db         - Test database connection"
echo "  test-redis      - Test Redis connection"
echo "  migrate         - Run database migrations"
echo "  tinker          - Laravel Tinker REPL"
echo "  serve           - Start development server"
echo ""
echo "========================================="

# Create helper commands
cat > /usr/local/bin/test-db << 'EOF'
#!/bin/bash
echo "Testing database connection..."
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Database connection successful!' . PHP_EOL;
    \$tables = \DB::select('SHOW TABLES');
    echo 'Tables found: ' . count(\$tables) . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"
EOF

cat > /usr/local/bin/test-redis << 'EOF'
#!/bin/bash
echo "Testing Redis connection..."
php artisan tinker --execute="
try {
    \Redis::ping();
    echo 'Redis connection successful!' . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"
EOF

cat > /usr/local/bin/migrate << 'EOF'
#!/bin/bash
php artisan migrate "$@"
EOF

cat > /usr/local/bin/tinker << 'EOF'
#!/bin/bash
php artisan tinker "$@"
EOF

cat > /usr/local/bin/serve << 'EOF'
#!/bin/bash
php artisan serve --host=0.0.0.0 --port=8000
EOF

# Make helper scripts executable
chmod +x /usr/local/bin/test-db
chmod +x /usr/local/bin/test-redis
chmod +x /usr/local/bin/migrate
chmod +x /usr/local/bin/tinker
chmod +x /usr/local/bin/serve

# Execute the command passed to docker run
exec "$@"