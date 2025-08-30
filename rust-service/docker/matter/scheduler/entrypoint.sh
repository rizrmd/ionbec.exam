#!/bin/bash

# Laravel Scheduler entrypoint script
set -e

echo "Starting Laravel Scheduler..."

# Wait for dependencies
sleep 5

# Run Laravel Scheduler (cron-like functionality)
cd /var/www
while true; do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done
