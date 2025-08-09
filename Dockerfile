FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Create necessary directories and set permissions
RUN mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy nginx configuration
RUN echo 'server { \n\
    listen 80; \n\
    index index.php index.html; \n\
    error_log  /var/log/nginx/error.log; \n\
    access_log /var/log/nginx/access.log; \n\
    root /var/www/public; \n\
    location ~ \.php$ { \n\
        try_files $uri =404; \n\
        fastcgi_split_path_info ^(.+\.php)(/.+)$; \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_index index.php; \n\
        include fastcgi_params; \n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \n\
        fastcgi_param PATH_INFO $fastcgi_path_info; \n\
    } \n\
    location / { \n\
        try_files $uri $uri/ /index.php?$query_string; \n\
        gzip_static on; \n\
    } \n\
}' > /etc/nginx/sites-available/default

# Copy supervisor configuration
RUN echo '[supervisord] \n\
nodaemon=true \n\
[program:nginx] \n\
command=/usr/sbin/nginx -g "daemon off;" \n\
autostart=true \n\
autorestart=true \n\
[program:php-fpm] \n\
command=/usr/local/sbin/php-fpm \n\
autostart=true \n\
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Generate application key if not exists
RUN php artisan key:generate --force || true

# Cache configuration
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Expose port 80
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord"]