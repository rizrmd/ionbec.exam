#!/bin/bash

# Laravel Horizon entrypoint script
set -e

echo "Starting Laravel Horizon..."

# Wait for dependencies
sleep 10

# Run Laravel Horizon
cd /var/www
php artisan horizon
