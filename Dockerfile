FROM php:8.1-fpm

# Install system dependencies including nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    nginx \
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

# Ensure static assets are accessible
RUN chmod -R 755 public/

# Configure nginx
COPY nginx.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Create necessary directories and set permissions
RUN mkdir -p storage/logs \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 777 storage \
    && chmod -R 777 bootstrap/cache \
    && chown -R www-data:www-data storage \
    && chown -R www-data:www-data bootstrap/cache

# Create startup script with nginx and php-fpm
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel application with Nginx..."\n\
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
# Override session config if needed\n\
if [ -f /var/www/config/session-override.php ]; then\n\
    echo "Applying session override configuration..."\n\
    cp /var/www/config/session-override.php /var/www/config/session.php\n\
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
# Ensure proper permissions at runtime\n\
echo "Setting proper permissions for storage..."\n\
chown -R www-data:www-data /var/www/storage\n\
chown -R www-data:www-data /var/www/bootstrap/cache\n\
chmod -R 777 /var/www/storage\n\
chmod -R 777 /var/www/bootstrap/cache\n\
\n\
# Start PHP-FPM in background\n\
echo "Starting PHP-FPM..."\n\
php-fpm -D\n\
\n\
# Start nginx in foreground\n\
echo "Starting Nginx on port 3000..."\n\
nginx -g "daemon off;"\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Simple healthcheck - just check if port is listening (ignore HTTP status)
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD nc -z localhost 3000 || exit 1

# Expose port 3000
EXPOSE 3000

# Start with our startup script
CMD ["/usr/local/bin/start.sh"]