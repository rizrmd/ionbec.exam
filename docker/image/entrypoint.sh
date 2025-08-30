#!/bin/bash
set -e

# Skip any npm commands at runtime since assets are already built
export SKIP_NPM=true
export NODE_ENV=production

# Ensure storage directories have correct permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Start Octane server
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
