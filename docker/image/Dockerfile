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

# Install composer dependencies as root (simpler approach)
RUN composer install --no-dev --optimize-autoloader

# Set proper permissions for www-data user (standard web user)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# The base image will handle the user switching
