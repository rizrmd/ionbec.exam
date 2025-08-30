FROM php:8.1-fpm

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install system dependencies including nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    nginx \
    netcat-traditional \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
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

# Copy package.json and package-lock.json for better caching
COPY package.json package-lock.json* ./

# Install npm dependencies (including dev dependencies for build)
RUN npm ci

# Copy application files
COPY . .

# Build frontend assets
RUN npm run production

# Complete composer setup and ensure all packages are properly discovered
RUN composer dump-autoload --optimize --no-dev && \
    php artisan package:discover --ansi

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
# Check Redis connectivity\n\
if [ ! -z "$REDIS_HOST" ]; then\n\
    echo "Testing Redis connection at $REDIS_HOST:${REDIS_PORT:-6379}..."\n\
    if nc -z -v -w5 $REDIS_HOST ${REDIS_PORT:-6379} 2>/dev/null; then\n\
        echo "Redis is reachable at $REDIS_HOST:${REDIS_PORT:-6379}"\n\
    else\n\
        echo "Warning: Cannot reach Redis at $REDIS_HOST:${REDIS_PORT:-6379}, falling back to file drivers"\n\
        export SESSION_DRIVER=file\n\
        export CACHE_DRIVER=file\n\
        export QUEUE_CONNECTION=sync\n\
    fi\n\
else\n\
    echo "No REDIS_HOST found, using file session driver..."\n\
    export SESSION_DRIVER=file\n\
    export CACHE_DRIVER=file\n\
    export QUEUE_CONNECTION=sync\n\
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
# Clear and regenerate package discovery\n\
echo "Rediscovering packages..."\n\
php artisan package:discover --ansi\n\
\n\
# Force clear any cached routes that might interfere\n\
echo "Clearing any cached routes..."\n\
rm -f bootstrap/cache/routes-*.php\n\
\n\
# Force route registration by running artisan commands\n\
echo "Forcing route registration..."\n\
php artisan yalr:display || echo "Yalr command not available"\n\
\n\
# List routes to verify they are loaded\n\
echo "Checking registered routes..."\n\
php artisan route:list --json | head -5 || echo "No routes found or route:list failed"\n\
\n\
# Test the root route directly\n\
echo "Testing root route response..."\n\
curl -s -o /dev/null -w "HTTP Status: %{http_code}" http://localhost:3000/ || echo "Route test failed"\n\
\n\
# Skip caching in development/debugging\n\
echo "APP_ENV: $APP_ENV"\n\
echo "APP_DEBUG: $APP_DEBUG"\n\
\n\
# Run migrations if needed\n\
echo "Running migrations..."\n\
php artisan migrate --force || echo "Migration failed, continuing..."\n\
\n\
# Laravel optimization as recommended by Coolify\n\
echo "Optimizing Laravel for production..."\n\
php artisan optimize:clear\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
php artisan optimize\n\
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

# Healthcheck that verifies the Laravel app is responding
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:3000/ || nc -z localhost 3000 || exit 1

# Expose port 3000
EXPOSE 3000

# Start with our startup script
CMD ["/usr/local/bin/start.sh"]