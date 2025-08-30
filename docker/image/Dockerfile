FROM ghcr.io/digital-entropy/dokar-php/octane:8.1

# Install PostgreSQL client and tools
RUN apt-get update && apt-get install -y \
    postgresql-client \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www

# Copy Laravel application files
COPY . /var/www

# Install composer dependencies as root
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Check if package.json exists and build assets if it does
RUN if [ -f "package.json" ]; then \
        npm ci && \
        npm run production && \
        rm -rf node_modules; \
    fi

# Run Laravel post-install scripts
RUN composer run-script post-autoload-dump

# Create necessary Laravel directories and set permissions
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

# Set proper permissions for www-data user
RUN chown -R www-data:www-data /var/www && \
    chmod -R 775 storage bootstrap/cache

# Create .env file if it doesn't exist
RUN if [ ! -f ".env" ]; then cp .env.example .env 2>/dev/null || echo "APP_ENV=production" > .env; fi

# Generate application key if not set
RUN php artisan key:generate --force || true

# Clear and cache config for production
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Copy and set entrypoint
COPY docker/image/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Set the entrypoint
ENTRYPOINT ["/entrypoint.sh"]
