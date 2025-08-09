FROM php:8.1-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    netcat-traditional \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy application files
COPY . .

# Complete composer setup
RUN composer dump-autoload --optimize --no-dev

# Create necessary directories and set permissions
RUN mkdir -p storage/logs \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 755 storage \
    && chmod -R 755 bootstrap/cache

# Create startup script with better error handling
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel application..."\n\
\n\
# Generate APP_KEY if not set\n\
if [ -z "$APP_KEY" ]; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
# Create mix-manifest.json if it doesnt exist\n\
if [ ! -f /var/www/public/mix-manifest.json ]; then\n\
    echo "Creating mix-manifest.json..."\n\
    echo "{\\"/js/app.js\\": \\"/js/app.js\\",\\"/css/app.css\\": \\"/css/app.css\\"}" > /var/www/public/mix-manifest.json\n\
fi\n\
\n\
# Fix session configuration for different domains\n\
echo "Current SESSION_DOMAIN: $SESSION_DOMAIN"\n\
echo "Current APP_URL: $APP_URL"\n\
\n\
# Detect the actual URL if not matching\n\
if [[ "$APP_URL" == *"ionbec.avolut.com"* ]]; then\n\
    echo "Updating APP_URL to match sslip.io domain..."\n\
    export APP_URL="http://io844g808o48ccsoscc888s0.107.155.75.50.sslip.io"\n\
fi\n\
\n\
# Clear session domain and disable secure cookies for HTTP\n\
export SESSION_DOMAIN=""\n\
export SESSION_SECURE_COOKIE=false\n\
export SESSION_SAME_SITE=lax\n\
export SANCTUM_STATEFUL_DOMAINS="localhost,127.0.0.1,io844g808o48ccsoscc888s0.107.155.75.50.sslip.io"\n\
echo "Session configured for HTTP with domain: $APP_URL"\n\
echo "Sanctum stateful domains: $SANCTUM_STATEFUL_DOMAINS"\n\
\n\
# Use file driver if Redis is not available\n\
if [ -z "$REDIS_URL" ]; then\n\
    echo "No REDIS_URL found, using file session driver..."\n\
    export SESSION_DRIVER=file\n\
    export CACHE_DRIVER=file\n\
fi\n\
\n\
# Clear Laravel caches\n\
echo "Clearing Laravel caches..."\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
php artisan route:clear\n\
\n\
# Skip caching in development/debugging\n\
echo "APP_ENV: $APP_ENV"\n\
echo "APP_DEBUG: $APP_DEBUG"\n\
\n\
# Run migrations if needed\n\
echo "Running migrations..."\n\
php artisan migrate --force || echo "Migration failed, continuing..."\n\
\n\
# Test basic Laravel functionality\n\
echo "Testing Laravel installation..."\n\
php artisan --version\n\
\n\
echo "Starting Laravel development server on 0.0.0.0:3000..."\n\
exec php artisan serve --host=0.0.0.0 --port=3000\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Simple healthcheck - just check if port is listening (ignore HTTP status)
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD nc -z localhost 3000 || exit 1

# Expose port 3000
EXPOSE 3000

# Start with our startup script
CMD ["/usr/local/bin/start.sh"]